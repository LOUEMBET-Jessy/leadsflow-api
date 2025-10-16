<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AutomationRule;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    /**
     * Get all automation rules for the account
     */
    public function index(Request $request): JsonResponse
    {
        $automations = AutomationRule::where('account_id', $request->user()->account_id)
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get();

        return response()->json(['automations' => $automations]);
    }

    /**
     * Get a specific automation rule
     */
    public function show(Request $request, AutomationRule $automation): JsonResponse
    {
        // Vérifier que l'automatisation appartient au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        return response()->json(['automation' => $automation]);
    }

    /**
     * Create a new automation rule
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'required|string|max:100',
            'action_type' => 'required|string|max:100',
            'parameters' => 'required|array',
            'is_active' => 'boolean',
            'priority' => 'integer|min:0',
        ]);

        $automation = AutomationRule::create([
            'account_id' => $request->user()->account_id,
            'name' => $request->name,
            'description' => $request->description,
            'trigger_type' => $request->trigger_type,
            'action_type' => $request->action_type,
            'parameters' => $request->parameters,
            'is_active' => $request->is_active ?? true,
            'priority' => $request->priority ?? 0,
        ]);

        return response()->json([
            'message' => 'Automation rule created successfully',
            'automation' => $automation,
        ], 201);
    }

    /**
     * Update an automation rule
     */
    public function update(Request $request, AutomationRule $automation): JsonResponse
    {
        // Vérifier que l'automatisation appartient au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_type' => 'sometimes|string|max:100',
            'action_type' => 'sometimes|string|max:100',
            'parameters' => 'sometimes|array',
            'is_active' => 'sometimes|boolean',
            'priority' => 'sometimes|integer|min:0',
        ]);

        $automation->update($request->only([
            'name', 'description', 'trigger_type', 'action_type', 'parameters', 'is_active', 'priority'
        ]));

        return response()->json([
            'message' => 'Automation rule updated successfully',
            'automation' => $automation,
        ]);
    }

    /**
     * Delete an automation rule
     */
    public function destroy(Request $request, AutomationRule $automation): JsonResponse
    {
        // Vérifier que l'automatisation appartient au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        $automation->delete();

        return response()->json([
            'message' => 'Automation rule deleted successfully',
        ]);
    }

    /**
     * Toggle automation rule status
     */
    public function toggle(Request $request, AutomationRule $automation): JsonResponse
    {
        // Vérifier que l'automatisation appartient au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        $automation->update(['is_active' => !$automation->is_active]);

        return response()->json([
            'message' => 'Automation rule status updated successfully',
            'automation' => $automation,
        ]);
    }

    /**
     * Test automation rule on a lead
     */
    public function test(Request $request, AutomationRule $automation, Lead $lead): JsonResponse
    {
        // Vérifier que l'automatisation et le lead appartiennent au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $context = $request->get('context', []);
        $canExecute = $automation->canExecute($lead, $context);

        return response()->json([
            'can_execute' => $canExecute,
            'automation' => $automation,
            'lead' => $lead,
        ]);
    }

    /**
     * Execute automation rule on a lead
     */
    public function execute(Request $request, AutomationRule $automation, Lead $lead): JsonResponse
    {
        // Vérifier que l'automatisation et le lead appartiennent au compte
        if ($automation->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Automation not found'], 404);
        }

        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $context = $request->get('context', []);
        $result = $automation->execute($lead, $context);

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Automation executed successfully' : 'Automation execution failed',
            'automation' => $automation,
            'lead' => $lead->fresh(),
        ]);
    }

    /**
     * Get automation statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $accountId = $request->user()->account_id;

        $stats = [
            'total' => AutomationRule::where('account_id', $accountId)->count(),
            'active' => AutomationRule::where('account_id', $accountId)
                ->where('is_active', true)
                ->count(),
            'by_trigger_type' => AutomationRule::where('account_id', $accountId)
                ->selectRaw('trigger_type, count(*) as count')
                ->groupBy('trigger_type')
                ->get()
                ->pluck('count', 'trigger_type'),
            'by_action_type' => AutomationRule::where('account_id', $accountId)
                ->selectRaw('action_type, count(*) as count')
                ->groupBy('action_type')
                ->get()
                ->pluck('count', 'action_type'),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get available trigger types
     */
    public function triggerTypes(): JsonResponse
    {
        $triggerTypes = [
            'lead_created' => 'Lead créé',
            'lead_updated' => 'Lead mis à jour',
            'lead_status_changed' => 'Statut du lead changé',
            'lead_stage_changed' => 'Étape du lead changée',
            'lead_score_updated' => 'Score du lead mis à jour',
            'lead_assigned' => 'Lead assigné',
            'interaction_created' => 'Interaction créée',
            'task_created' => 'Tâche créée',
            'task_completed' => 'Tâche complétée',
            'task_overdue' => 'Tâche en retard',
        ];

        return response()->json(['trigger_types' => $triggerTypes]);
    }

    /**
     * Get available action types
     */
    public function actionTypes(): JsonResponse
    {
        $actionTypes = [
            'assign_user' => 'Assigner un utilisateur',
            'update_status' => 'Mettre à jour le statut',
            'update_stage' => 'Mettre à jour l\'étape',
            'send_email' => 'Envoyer un email',
            'create_task' => 'Créer une tâche',
            'update_score' => 'Mettre à jour le score',
            'add_note' => 'Ajouter une note',
            'send_notification' => 'Envoyer une notification',
        ];

        return response()->json(['action_types' => $actionTypes]);
    }
}
