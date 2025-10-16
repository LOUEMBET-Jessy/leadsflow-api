<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Lead\StoreLeadRequest;
use App\Http\Requests\Api\V1\Lead\UpdateLeadRequest;
use App\Models\Lead;
use App\Models\LeadAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Get all leads for the account
     */
    public function index(Request $request): JsonResponse
    {
        $query = Lead::where('account_id', $request->user()->account_id)
            ->with(['currentStage.pipeline', 'assignedUsers', 'interactions', 'tasks']);

        // Filtres
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('stage_id')) {
            $query->where('current_stage_id', $request->stage_id);
        }

        if ($request->has('assigned_to')) {
            $query->whereHas('assignedUsers', function ($q) use ($request) {
                $q->where('user_id', $request->assigned_to);
            });
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Tri
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $leads = $query->paginate($request->get('per_page', 20));

        return response()->json($leads);
    }

    /**
     * Get a specific lead
     */
    public function show(Request $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->load([
            'currentStage.pipeline',
            'assignedUsers',
            'interactions.user',
            'tasks.user'
        ]);

        return response()->json(['lead' => $lead]);
    }

    /**
     * Create a new lead
     */
    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create([
            'account_id' => $request->user()->account_id,
            'current_stage_id' => $request->current_stage_id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'status' => $request->status ?? 'Nouveau',
            'source' => $request->source,
            'location' => $request->location,
            'score' => $request->score ?? 0,
            'estimated_value' => $request->estimated_value,
            'notes' => $request->notes,
            'custom_fields' => $request->custom_fields,
        ]);

        // Assigner le lead si spécifié
        if ($request->has('assigned_user_id')) {
            $lead->assignedUsers()->attach($request->assigned_user_id, [
                'assigned_at' => now(),
                'assigned_by_user_id' => $request->user()->id,
                'notes' => 'Assigné lors de la création'
            ]);
        }

        $lead->load(['currentStage.pipeline', 'assignedUsers']);

        return response()->json([
            'message' => 'Lead created successfully',
            'lead' => $lead,
        ], 201);
    }

    /**
     * Update a lead
     */
    public function update(UpdateLeadRequest $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->update($request->validated());

        $lead->load(['currentStage.pipeline', 'assignedUsers']);

        return response()->json([
            'message' => 'Lead updated successfully',
            'lead' => $lead,
        ]);
    }

    /**
     * Delete a lead
     */
    public function destroy(Request $request, Lead $lead): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->delete();

        return response()->json([
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Assign lead to user(s)
     */
    public function assign(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'notes' => 'nullable|string|max:500',
        ]);

        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        // Vérifier que les utilisateurs appartiennent au même compte
        $validUserIds = User::where('account_id', $request->user()->account_id)
            ->whereIn('id', $request->user_ids)
            ->pluck('id');

        $assignments = [];
        foreach ($validUserIds as $userId) {
            $assignments[$userId] = [
                'assigned_at' => now(),
                'assigned_by_user_id' => $request->user()->id,
                'notes' => $request->notes,
            ];
        }

        $lead->assignedUsers()->syncWithoutDetaching($assignments);

        return response()->json([
            'message' => 'Lead assigned successfully',
            'lead' => $lead->load('assignedUsers'),
        ]);
    }

    /**
     * Unassign lead from user
     */
    public function unassign(Request $request, Lead $lead, User $user): JsonResponse
    {
        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $lead->assignedUsers()->detach($user->id);

        return response()->json([
            'message' => 'Lead unassigned successfully',
        ]);
    }

    /**
     * Update lead score
     */
    public function updateScore(Request $request, Lead $lead): JsonResponse
    {
        $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'reason' => 'nullable|string|max:255',
        ]);

        // Vérifier que le lead appartient au compte
        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $oldScore = $lead->score;
        $lead->update(['score' => $request->score]);

        // Créer une interaction pour documenter le changement
        $lead->interactions()->create([
            'user_id' => $request->user()->id,
            'type' => 'Note',
            'subject' => 'Score mis à jour',
            'summary' => "Score changé de {$oldScore} à {$request->score}",
            'details' => $request->reason ?? 'Score mis à jour manuellement',
            'date' => now(),
        ]);

        return response()->json([
            'message' => 'Lead score updated successfully',
            'lead' => $lead,
        ]);
    }

    /**
     * Get lead statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $stats = [
            'total' => Lead::where('account_id', $accountId)->count(),
            'by_status' => Lead::where('account_id', $accountId)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
            'by_source' => Lead::where('account_id', $accountId)
                ->selectRaw('source, count(*) as count')
                ->whereNotNull('source')
                ->groupBy('source')
                ->get()
                ->pluck('count', 'source'),
            'by_stage' => Lead::where('account_id', $accountId)
                ->join('stages', 'leads.current_stage_id', '=', 'stages.id')
                ->selectRaw('stages.name, count(*) as count')
                ->groupBy('stages.name')
                ->get()
                ->pluck('count', 'name'),
            'recent' => Lead::where('account_id', $accountId)
                ->where('created_at', '>=', now()->subDays(30))
                ->count(),
            'high_score' => Lead::where('account_id', $accountId)
                ->where('score', '>=', 80)
                ->count(),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Capture lead from web form (public endpoint)
     */
    public function captureWebForm(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:100',
            'api_key' => 'required|string',
        ]);

        // Vérifier la clé API (à implémenter selon votre logique)
        $account = Account::where('api_key', $request->api_key)->first();
        if (!$account) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        $lead = Lead::create([
            'account_id' => $account->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'company' => $request->company,
            'source' => $request->source ?? 'Web Form',
            'status' => 'Nouveau',
            'score' => 50, // Score par défaut pour les leads web
        ]);

        return response()->json([
            'message' => 'Lead captured successfully',
            'lead_id' => $lead->id,
        ], 201);
    }
}
