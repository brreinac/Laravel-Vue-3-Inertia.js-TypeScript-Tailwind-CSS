<?php

namespace App\Http\Requests;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['nullable', Rule::in(ProjectStatus::values())],
            'owner_id' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del proyecto es obligatorio.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'owner_id.exists' => 'El responsable seleccionado no existe.',
            'status.in' => 'El estado del proyecto no es válido.',
        ];
    }
}
