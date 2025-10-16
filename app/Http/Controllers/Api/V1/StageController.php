<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Get all stages for a pipeline
     */
    public function index(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $stages = $pipeline->stages()
            ->withCount('leads')
            ->orderBy('order')
            ->get();

        return response()->json(['stages' => $stages]);
    }

    /**
     * Get a specific stage
     */
    public function show(Request $request, Stage $stage): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($stage->pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        $stage->load(['pipeline', 'leads' => function ($query) {
            $query->with(['assignedUsers', 'interactions']);
        }]);

        return response()->json(['stage' => $stage]);
    }

    /**
     * Create a new stage
     */
    public function store(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_final' => 'boolean',
        ]);

        // Déterminer l'ordre
        $maxOrder = $pipeline->stages()->max('order') ?? 0;
        $order = $request->get('order', $maxOrder + 1);

        $stage = $pipeline->stages()->create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#3498db',
            'order' => $order,
            'is_final' => $request->is_final ?? false,
        ]);

        return response()->json([
            'message' => 'Stage created successfully',
            'stage' => $stage,
        ], 201);
    }

    /**
     * Update a stage
     */
    public function update(Request $request, Stage $stage): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($stage->pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_final' => 'sometimes|boolean',
        ]);

        $stage->update($request->only(['name', 'description', 'color', 'is_final']));

        return response()->json([
            'message' => 'Stage updated successfully',
            'stage' => $stage,
        ]);
    }

    /**
     * Delete a stage
     */
    public function destroy(Request $request, Stage $stage): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($stage->pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        // Vérifier qu'il n'y a pas de leads dans cette étape
        $leadsCount = $stage->leads()->count();
        if ($leadsCount > 0) {
            return response()->json([
                'message' => "Cannot delete stage with {$leadsCount} leads. Please move leads to another stage first.",
            ], 400);
        }

        $stage->delete();

        return response()->json([
            'message' => 'Stage deleted successfully',
        ]);
    }

    /**
     * Reorder stages
     */
    public function reorder(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:stages,id',
            'stages.*.order' => 'required|integer',
        ]);

        foreach ($request->stages as $stageData) {
            Stage::where('id', $stageData['id'])
                ->where('pipeline_id', $pipeline->id)
                ->update(['order' => $stageData['order']]);
        }

        return response()->json([
            'message' => 'Stages reordered successfully',
        ]);
    }

    /**
     * Move lead to stage
     */
    public function moveLead(Request $request, Stage $stage): JsonResponse
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
        ]);

        // Vérifier que l'étape appartient au compte
        if ($stage->pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        $lead = Lead::findOrFail($request->lead_id);

        // Vérifier que le lead appartient au même compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $oldStage = $lead->currentStage;
        $lead->update(['current_stage_id' => $stage->id]);

        // Créer une interaction pour documenter le changement
        $lead->interactions()->create([
            'user_id' => $request->user()->id,
            'type' => 'Note',
            'subject' => 'Lead déplacé',
            'summary' => "Lead déplacé de '{$oldStage?->name}' vers '{$stage->name}'",
            'details' => 'Lead déplacé manuellement',
            'date' => now(),
        ]);

        return response()->json([
            'message' => 'Lead moved successfully',
            'lead' => $lead->load('currentStage'),
        ]);
    }

    /**
     * Get stage statistics
     */
    public function stats(Request $request, Stage $stage): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($stage->pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Stage not found'], 404);
        }

        $stats = [
            'leads_count' => $stage->leads()->count(),
            'conversion_rate' => $stage->conversion_rate,
            'average_time_in_stage' => $this->getAverageTimeInStage($stage),
            'leads_by_status' => $stage->leads()
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Calculate average time in stage
     */
    protected function getAverageTimeInStage(Stage $stage): ?float
    {
        $leads = $stage->leads()
            ->whereNotNull('updated_at')
            ->get();

        if ($leads->isEmpty()) {
            return null;
        }

        $totalDays = $leads->sum(function ($lead) {
            return $lead->updated_at->diffInDays(now());
        });

        return round($totalDays / $leads->count(), 2);
    }
}
