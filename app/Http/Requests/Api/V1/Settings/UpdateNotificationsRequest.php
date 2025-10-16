<?php

namespace App\Http\Requests\Api\V1\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email_notifications' => 'nullable|boolean',
            'push_notifications' => 'nullable|boolean',
            'lead_assigned' => 'nullable|boolean',
            'lead_status_changed' => 'nullable|boolean',
            'task_due' => 'nullable|boolean',
            'task_overdue' => 'nullable|boolean',
            'new_message' => 'nullable|boolean',
            'weekly_summary' => 'nullable|boolean',
            'monthly_report' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email_notifications.boolean' => 'Les notifications email doivent être activées ou désactivées.',
            'push_notifications.boolean' => 'Les notifications push doivent être activées ou désactivées.',
            'lead_assigned.boolean' => 'La notification de lead assigné doit être activée ou désactivée.',
            'lead_status_changed.boolean' => 'La notification de changement de statut doit être activée ou désactivée.',
            'task_due.boolean' => 'La notification de tâche due doit être activée ou désactivée.',
            'task_overdue.boolean' => 'La notification de tâche en retard doit être activée ou désactivée.',
            'new_message.boolean' => 'La notification de nouveau message doit être activée ou désactivée.',
            'weekly_summary.boolean' => 'Le résumé hebdomadaire doit être activé ou désactivé.',
            'monthly_report.boolean' => 'Le rapport mensuel doit être activé ou désactivé.',
        ];
    }
}
