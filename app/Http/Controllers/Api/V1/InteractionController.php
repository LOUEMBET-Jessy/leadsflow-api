<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Interaction;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InteractionController extends Controller
{
    /**
     * Get all interactions for a lead
     */
    public function index(Request $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $query = $lead->interactions()->with('user');

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('outcome')) {
            $query->where('outcome', $request->outcome);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $interactions = $query->paginate($request->get('per_page', 20));

        return response()->json($interactions);
    }

    /**
     * Get a specific interaction
     */
    public function show(Request $request, Interaction $interaction): JsonResponse
    {
        // Vérifier que l'interaction appartient au compte
        if ($interaction->lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Interaction not found'], 404);
        }

        $interaction->load(['lead', 'user']);

        return response()->json(['interaction' => $interaction]);
    }

    /**
     * Create a new interaction
     */
    public function store(Request $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $request->validate([
            'type' => 'required|in:Email,Appel,Reunion,Note,SMS,Chat',
            'subject' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:1000',
            'details' => 'nullable|string',
            'date' => 'required|date',
            'duration' => 'nullable|integer|min:0',
            'outcome' => 'nullable|in:positive,neutral,negative,follow_up_required',
            'metadata' => 'nullable|array',
        ]);

        $interaction = $lead->interactions()->create([
            'user_id' => $request->user()->id,
            'type' => $request->type,
            'subject' => $request->subject,
            'summary' => $request->summary,
            'details' => $request->details,
            'date' => $request->date,
            'duration' => $request->duration,
            'outcome' => $request->outcome,
            'metadata' => $request->metadata,
        ]);

        // Mettre à jour la dernière interaction du lead
        $lead->update(['last_contact_at' => $interaction->date]);

        $interaction->load('user');

        return response()->json([
            'message' => 'Interaction created successfully',
            'interaction' => $interaction,
        ], 201);
    }

    /**
     * Update an interaction
     */
    public function update(Request $request, Interaction $interaction): JsonResponse
    {
        // Vérifier que l'interaction appartient au compte
        if ($interaction->lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Interaction not found'], 404);
        }

        $request->validate([
            'type' => 'sometimes|in:Email,Appel,Reunion,Note,SMS,Chat',
            'subject' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:1000',
            'details' => 'nullable|string',
            'date' => 'sometimes|date',
            'duration' => 'nullable|integer|min:0',
            'outcome' => 'nullable|in:positive,neutral,negative,follow_up_required',
            'metadata' => 'nullable|array',
        ]);

        $interaction->update($request->only([
            'type', 'subject', 'summary', 'details', 'date', 'duration', 'outcome', 'metadata'
        ]));

        $interaction->load('user');

        return response()->json([
            'message' => 'Interaction updated successfully',
            'interaction' => $interaction,
        ]);
    }

    /**
     * Delete an interaction
     */
    public function destroy(Request $request, Interaction $interaction): JsonResponse
    {
        // Vérifier que l'interaction appartient au compte
        if ($interaction->lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Interaction not found'], 404);
        }

        $interaction->delete();

        return response()->json([
            'message' => 'Interaction deleted successfully',
        ]);
    }

    /**
     * Get interaction statistics
     */
    public function stats(Request $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $stats = [
            'total' => $lead->interactions()->count(),
            'by_type' => $lead->interactions()
                ->selectRaw('type, count(*) as count')
                ->groupBy('type')
                ->get()
                ->pluck('count', 'type'),
            'by_outcome' => $lead->interactions()
                ->selectRaw('outcome, count(*) as count')
                ->whereNotNull('outcome')
                ->groupBy('outcome')
                ->get()
                ->pluck('count', 'outcome'),
            'total_duration' => $lead->interactions()
                ->whereNotNull('duration')
                ->sum('duration'),
            'average_duration' => $lead->interactions()
                ->whereNotNull('duration')
                ->avg('duration'),
            'last_interaction' => $lead->interactions()
                ->orderBy('date', 'desc')
                ->first(),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get all interactions for the account
     */
    public function all(Request $request): JsonResponse
    {
        $query = Interaction::whereHas('lead', function ($q) use ($request) {
            $q->where('account_id', $request->user()->account_id);
        })->with(['lead', 'user']);

        // Filtres
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        // Tri
        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $interactions = $query->paginate($request->get('per_page', 20));

        return response()->json($interactions);
    }

    /**
     * Get recent interactions
     */
    public function recent(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        $interactions = Interaction::whereHas('lead', function ($q) use ($request) {
            $q->where('account_id', $request->user()->account_id);
        })
        ->with(['lead', 'user'])
        ->orderBy('date', 'desc')
        ->limit($limit)
        ->get();

        return response()->json(['interactions' => $interactions]);
    }
}
