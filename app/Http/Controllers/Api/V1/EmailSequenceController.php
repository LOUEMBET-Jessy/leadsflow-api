<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmailSequence;
use App\Models\SequenceStep;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailSequenceController extends Controller
{
    /**
     * Get all email sequences for the account
     */
    public function index(Request $request): JsonResponse
    {
        $sequences = EmailSequence::where('account_id', $request->user()->account_id)
            ->with(['steps' => function ($query) {
                $query->orderBy('order');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['sequences' => $sequences]);
    }

    /**
     * Get a specific email sequence
     */
    public function show(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $sequence->load(['steps' => function ($query) {
            $query->orderBy('order');
        }]);

        return response()->json(['sequence' => $sequence]);
    }

    /**
     * Create a new email sequence
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_conditions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $sequence = EmailSequence::create([
            'account_id' => $request->user()->account_id,
            'name' => $request->name,
            'description' => $request->description,
            'trigger_conditions' => $request->trigger_conditions,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Email sequence created successfully',
            'sequence' => $sequence,
        ], 201);
    }

    /**
     * Update an email sequence
     */
    public function update(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'trigger_conditions' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $sequence->update($request->only([
            'name', 'description', 'trigger_conditions', 'is_active'
        ]));

        return response()->json([
            'message' => 'Email sequence updated successfully',
            'sequence' => $sequence,
        ]);
    }

    /**
     * Delete an email sequence
     */
    public function destroy(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $sequence->delete();

        return response()->json([
            'message' => 'Email sequence deleted successfully',
        ]);
    }

    /**
     * Add step to email sequence
     */
    public function addStep(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'email_template' => 'required|string',
            'text_template' => 'nullable|string',
            'delay_days' => 'required|integer|min:0',
            'personalization_tags' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        // Déterminer l'ordre
        $maxOrder = $sequence->steps()->max('order') ?? 0;
        $order = $request->get('order', $maxOrder + 1);

        $step = $sequence->steps()->create([
            'subject' => $request->subject,
            'email_template' => $request->email_template,
            'text_template' => $request->text_template,
            'delay_days' => $request->delay_days,
            'personalization_tags' => $request->personalization_tags ?? [],
            'order' => $order,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Step added successfully',
            'step' => $step,
        ], 201);
    }

    /**
     * Update step in email sequence
     */
    public function updateStep(Request $request, SequenceStep $step): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($step->sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Step not found'], 404);
        }

        $request->validate([
            'subject' => 'sometimes|string|max:255',
            'email_template' => 'sometimes|string',
            'text_template' => 'nullable|string',
            'delay_days' => 'sometimes|integer|min:0',
            'personalization_tags' => 'nullable|array',
            'is_active' => 'sometimes|boolean',
        ]);

        $step->update($request->only([
            'subject', 'email_template', 'text_template', 'delay_days', 'personalization_tags', 'is_active'
        ]));

        return response()->json([
            'message' => 'Step updated successfully',
            'step' => $step,
        ]);
    }

    /**
     * Delete step from email sequence
     */
    public function deleteStep(Request $request, SequenceStep $step): JsonResponse
    {
        // Vérifier que l'étape appartient au compte
        if ($step->sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Step not found'], 404);
        }

        $step->delete();

        return response()->json([
            'message' => 'Step deleted successfully',
        ]);
    }

    /**
     * Reorder steps in email sequence
     */
    public function reorderSteps(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $request->validate([
            'steps' => 'required|array',
            'steps.*.id' => 'required|exists:sequence_steps,id',
            'steps.*.order' => 'required|integer',
        ]);

        foreach ($request->steps as $stepData) {
            SequenceStep::where('id', $stepData['id'])
                ->where('sequence_id', $sequence->id)
                ->update(['order' => $stepData['order']]);
        }

        return response()->json([
            'message' => 'Steps reordered successfully',
        ]);
    }

    /**
     * Enroll lead in email sequence
     */
    public function enrollLead(Request $request, EmailSequence $sequence, Lead $lead): JsonResponse
    {
        // Vérifier que la séquence et le lead appartiennent au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        if ($lead->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Lead not found'], 404);
        }

        $enrollment = $sequence->enrollLead($lead);

        if (!$enrollment) {
            return response()->json([
                'message' => 'Lead does not meet sequence criteria',
            ], 400);
        }

        return response()->json([
            'message' => 'Lead enrolled successfully',
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Get sequence statistics
     */
    public function stats(Request $request, EmailSequence $sequence): JsonResponse
    {
        // Vérifier que la séquence appartient au compte
        if ($sequence->account_id !== $request->user()->account_id) {
            return response()->json(['message' => 'Email sequence not found'], 404);
        }

        $stats = [
            'total_enrollments' => $sequence->total_enrollments,
            'active_enrollments' => $sequence->active_enrollments,
            'completion_rate' => $sequence->completion_rate,
            'steps_count' => $sequence->steps()->count(),
        ];

        return response()->json(['stats' => $stats]);
    }

    /**
     * Get available personalization tags
     */
    public function personalizationTags(): JsonResponse
    {
        $tags = [
            'first_name' => 'Prénom',
            'last_name' => 'Nom',
            'full_name' => 'Nom complet',
            'email' => 'Email',
            'company' => 'Entreprise',
            'phone' => 'Téléphone',
            'location' => 'Localisation',
            'source' => 'Source',
            'score' => 'Score',
            'status' => 'Statut',
            'stage_name' => 'Nom de l\'étape',
            'pipeline_name' => 'Nom du pipeline',
        ];

        return response()->json(['tags' => $tags]);
    }
}
