<?php

namespace App\Http\Requests\Api\V1\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|in:low,medium,high',
            'status' => 'nullable|in:todo,in_progress,completed',
            'assigned_to_user_id' => 'sometimes|required|exists:users,id',
            'lead_id' => 'nullable|exists:leads,id',
            'reminders' => 'nullable|array',
            'reminders.*' => 'date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre de la tâche est requis.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'due_date.date' => 'La date d\'échéance doit être une date valide.',
            'priority.in' => 'La priorité doit être low, medium ou high.',
            'status.in' => 'Le statut doit être todo, in_progress ou completed.',
            'assigned_to_user_id.required' => 'L\'utilisateur assigné est requis.',
            'assigned_to_user_id.exists' => 'L\'utilisateur assigné n\'existe pas.',
            'lead_id.exists' => 'Le lead sélectionné n\'existe pas.',
            'reminders.array' => 'Les rappels doivent être un tableau.',
            'reminders.*.date' => 'Chaque rappel doit être une date valide.',
        ];
    }
}
