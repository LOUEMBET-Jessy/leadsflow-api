<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Pipeline\StorePipelineRequest;
use App\Http\Requests\Api\V1\Pipeline\UpdatePipelineRequest;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    /**
     * Get all pipelines for the account
     */
    public function index(Request $request): JsonResponse
    {
        $pipelines = Pipeline::where('account_id', $request->user()->account_id)
            ->with(['stages' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['pipelines' => $pipelines]);
    }

    /**
     * Get a specific pipeline
     */
    public function show(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $pipeline->load(['stages' => function ($query) {
            $query->orderBy('order');
        }]);

        return response()->json(['pipeline' => $pipeline]);
    }

    /**
     * Create a new pipeline
     */
    public function store(StorePipelineRequest $request): JsonResponse
    {
        $pipeline = Pipeline::create([
            'account_id' => $request->user()->account_id,
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => $request->is_active ?? true,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        // Créer les étapes par défaut si spécifiées
        if ($request->has('default_stages') && $request->default_stages) {
            $this->createDefaultStages($pipeline);
        }

        $pipeline->load('stages');

        return response()->json([
            'message' => 'Pipeline created successfully',
            'pipeline' => $pipeline,
        ], 201);
    }

    /**
     * Update a pipeline
     */
    public function update(UpdatePipelineRequest $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $pipeline->update($request->validated());

        $pipeline->load('stages');

        return response()->json([
            'message' => 'Pipeline updated successfully',
            'pipeline' => $pipeline,
        ]);
    }

    /**
     * Delete a pipeline
     */
    public function destroy(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        // Vérifier qu'il n'y a pas de leads dans ce pipeline
        $leadsCount = $pipeline->leads()->count();
        if ($leadsCount > 0) {
            return response()->json([
                'message' => "Cannot delete pipeline with {$leadsCount} leads. Please reassign leads first.",
            ], 400);
        }

        $pipeline->delete();

        return response()->json([
            'message' => 'Pipeline deleted successfully',
        ]);
    }

    /**
     * Get pipeline statistics
     */
    public function stats(Request $request, Pipeline $pipeline): JsonResponse
    {
        // Vérifier que le pipeline appartient au compte
        if ($pipeline->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Pipeline not found'], 404);
        }

        $stats = [
            'total_leads' => $pipeline->leads()->count(),
            'by_stage' => $pipeline->stages()->withCount('leads')->get()->map(function ($stage) {
                return [
                    'stage_id' => $stage->id,
                    'stage_name' => $stage->name,
                    'leads_count' => $stage->leads_count,
                ];
            }),
            'conversion_rate' => $pipeline->conversion_rate,
            'average_time_in_pipeline' => $this->getAverageTimeInPipeline($pipeline),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Reorder pipelines
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate([
            'pipelines' => 'required|array',
            'pipelines.*.id' => 'required|exists:pipelines,id',
            'pipelines.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->pipelines as $pipelineData) {
            Pipeline::where('id', $pipelineData['id'])
                ->where('account_id', $request->user()->account_id)
                ->update(['sort_order' => $pipelineData['sort_order']]);
        }

        return response()->json([
            'message' => 'Pipelines reordered successfully',
        ]);
    }

    /**
     * Create default stages for a pipeline
     */
    protected function createDefaultStages(Pipeline $pipeline): void
    {
        $defaultStages = [
            ['name' => 'Nouveau', 'color' => '#3498db', 'order' => 1],
            ['name' => 'Contacté', 'color' => '#f39c12', 'order' => 2],
            ['name' => 'Qualification', 'color' => '#e67e22', 'order' => 3],
            ['name' => 'Négociation', 'color' => '#e74c3c', 'order' => 4],
            ['name' => 'Gagné', 'color' => '#27ae60', 'order' => 5, 'is_final' => true],
            ['name' => 'Perdu', 'color' => '#95a5a6', 'order' => 6, 'is_final' => true],
        ];

        foreach ($defaultStages as $stageData) {
            $pipeline->stages()->create($stageData);
        }
    }

    /**
     * Calculate average time in pipeline
     */
    protected function getAverageTimeInPipeline(Pipeline $pipeline): ?float
    {
        $leads = $pipeline->leads()
            ->whereNotNull('created_at')
            ->whereNotNull('updated_at')
            ->get();

        if ($leads->isEmpty()) {
            return null;
        }

        $totalDays = $leads->sum(function ($lead) {
            return $lead->created_at->diffInDays($lead->updated_at);
        });

        return round($totalDays / $leads->count(), 2);
    }
}
