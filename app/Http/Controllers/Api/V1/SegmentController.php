<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Segment;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SegmentController extends Controller
{
    /**
     * Get all segments for the account
     */
    public function index(Request $request): JsonResponse
    {
        $segments = Segment::where('account_id', $request->user()->account_id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['segments' => $segments]);
    }

    /**
     * Get a specific segment
     */
    public function show(Request $request, Segment $segment): JsonResponse
    {
        // Vérifier que le segment appartient au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        return response()->json(['segment' => $segment]);
    }

    /**
     * Create a new segment
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'criteria' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $segment = Segment::create([
            'account_id' => $request->user()->account_id,
            'name' => $request->name,
            'description' => $request->description,
            'criteria' => $request->criteria,
            'is_active' => $request->is_active ?? true,
        ]);

        // Mettre à jour le nombre de leads
        $segment->updateLeadCount();

        return response()->json([
            'message' => 'Segment created successfully',
            'segment' => $segment,
        ], 201);
    }

    /**
     * Update a segment
     */
    public function update(Request $request, Segment $segment): JsonResponse
    {
        // Vérifier que le segment appartient au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'criteria' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $segment->update($request->only([
            'name', 'description', 'criteria', 'is_active'
        ]));

        // Mettre à jour le nombre de leads si les critères ont changé
        if ($request->has('criteria')) {
            $segment->updateLeadCount();
        }

        return response()->json([
            'message' => 'Segment updated successfully',
            'segment' => $segment,
        ]);
    }

    /**
     * Delete a segment
     */
    public function destroy(Request $request, Segment $segment): JsonResponse
    {
        // Vérifier que le segment appartient au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        $segment->delete();

        return response()->json([
            'message' => 'Segment deleted successfully',
        ]);
    }

    /**
     * Get leads in segment
     */
    public function leads(Request $request, Segment $segment): JsonResponse
    {
        // Vérifier que le segment appartient au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        $leads = $segment->getLeads();

        return response()->json(['leads' => $leads]);
    }

    /**
     * Test segment criteria on a lead
     */
    public function testLead(Request $request, Segment $segment, Lead $lead): JsonResponse
    {
        // Vérifier que le segment et le lead appartiennent au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $matches = $segment->matchesLead($lead);

        return response()->json([
            'matches' => $matches,
            'segment' => $segment,
            'lead' => $lead,
        ]);
    }

    /**
     * Update segment lead count
     */
    public function updateCount(Request $request, Segment $segment): JsonResponse
    {
        // Vérifier que le segment appartient au compte
        if ($segment->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Segment not found'], 404);
        }

        $segment->updateLeadCount();

        return response()->json([
            'message' => 'Segment count updated successfully',
            'segment' => $segment,
        ]);
    }

    /**
     * Get segment statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $stats = [
            'total' => Segment::where('account_id', $accountId)->count(),
            'active' => Segment::where('account_id', $accountId)
                ->where('is_active', true)
                ->count(),
            'total_leads' => Segment::where('account_id', $accountId)
                ->sum('lead_count'),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get available field operators
     */
    public function fieldOperators(): JsonResponse
    {
        $operators = [
            'equals' => 'Est égal à',
            'not_equals' => 'N\'est pas égal à',
            'greater_than' => 'Est supérieur à',
            'less_than' => 'Est inférieur à',
            'greater_than_or_equal' => 'Est supérieur ou égal à',
            'less_than_or_equal' => 'Est inférieur ou égal à',
            'contains' => 'Contient',
            'not_contains' => 'Ne contient pas',
            'in' => 'Est dans',
            'not_in' => 'N\'est pas dans',
            'is_null' => 'Est vide',
            'is_not_null' => 'N\'est pas vide',
            'between' => 'Est entre',
            'not_between' => 'N\'est pas entre',
            'date_between' => 'Date entre',
            'date_after' => 'Date après',
            'date_before' => 'Date avant',
        ];

        return response()->json(['operators' => $operators]);
    }

    /**
     * Get available fields for segmentation
     */
    public function availableFields(): JsonResponse
    {
        $fields = [
            'name' => 'Nom',
            'email' => 'Email',
            'phone' => 'Téléphone',
            'company' => 'Entreprise',
            'status' => 'Statut',
            'source' => 'Source',
            'location' => 'Localisation',
            'score' => 'Score',
            'estimated_value' => 'Valeur estimée',
            'stage_name' => 'Nom de l\'étape',
            'pipeline_name' => 'Nom du pipeline',
            'days_since_created' => 'Jours depuis création',
            'days_since_last_contact' => 'Jours depuis dernier contact',
            'interaction_count' => 'Nombre d\'interactions',
            'task_count' => 'Nombre de tâches',
            'open_task_count' => 'Nombre de tâches ouvertes',
        ];

        return response()->json(['fields' => $fields]);
    }
}
