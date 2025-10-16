<?php

namespace App\Http\Requests\Api\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
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
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('leads', 'email')->ignore($this->lead->id)
            ],
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'status_id' => 'sometimes|required|exists:lead_statuses,id',
            'pipeline_stage_id' => 'nullable|exists:pipeline_stages,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'priority' => 'nullable|in:Hot,Warm,Cold',
            'notes' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'company_size' => 'nullable|in:Small,Medium,Large',
            'custom_fields' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est requis.',
            'first_name.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            'last_name.required' => 'Le nom est requis.',
            'last_name.max' => 'Le nom ne peut pas dépasser 255 caractères.',
            'email.required' => 'L\'adresse email est requise.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée pour un autre lead.',
            'phone.max' => 'Le numéro de téléphone ne peut pas dépasser 20 caractères.',
            'company.max' => 'Le nom de l\'entreprise ne peut pas dépasser 255 caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'source.max' => 'La source ne peut pas dépasser 255 caractères.',
            'status_id.required' => 'Le statut est requis.',
            'status_id.exists' => 'Le statut sélectionné n\'existe pas.',
            'pipeline_stage_id.exists' => 'L\'étape du pipeline sélectionnée n\'existe pas.',
            'assigned_to_user_id.exists' => 'L\'utilisateur assigné n\'existe pas.',
            'priority.in' => 'La priorité doit être Hot, Warm ou Cold.',
            'address.max' => 'L\'adresse ne peut pas dépasser 255 caractères.',
            'city.max' => 'La ville ne peut pas dépasser 255 caractères.',
            'country.max' => 'Le pays ne peut pas dépasser 255 caractères.',
            'industry.max' => 'L\'industrie ne peut pas dépasser 255 caractères.',
            'company_size.in' => 'La taille de l\'entreprise doit être Small, Medium ou Large.',
            'custom_fields.array' => 'Les champs personnalisés doivent être un tableau.',
        ];
    }
}
