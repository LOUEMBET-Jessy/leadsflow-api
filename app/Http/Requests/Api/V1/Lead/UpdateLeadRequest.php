<?php

namespace App\Http\Requests\Api\V1\Lead;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'status' => 'sometimes|in:Nouveau,Contacté,Qualification,Négociation,Gagné,Perdu,Chaud,Froid,A_recontacter,Non_qualifié',
            'source' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'score' => 'sometimes|integer|min:0|max:100',
            'estimated_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
            'current_stage_id' => 'nullable|exists:stages,id',
        ];
    }
}