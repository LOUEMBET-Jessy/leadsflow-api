<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'nullable|in:Admin,Manager,Commercial,Marketing,GestLead',
            'account_name' => 'required|string|max:255',
            'account_slug' => 'required|string|max:255|unique:accounts,slug',
        ];
    }

    public function messages(): array
    {
        return [
            'account_slug.unique' => 'Ce nom de compte est déjà utilisé.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
        ];
    }
}