<?php

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'priority' => ['sometimes', Rule::in(TaskPriority::values())],
            'project_id' => ['sometimes', 'integer', 'exists:projects,id'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'status' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.prohibited' => 'Use el endpoint de cambio de estado para actualizar el estado.',
            'priority.in' => 'La prioridad no es válida.',
            'project_id.exists' => 'El proyecto seleccionado no existe.',
            'assigned_to.exists' => 'El usuario seleccionado no existe.',
        ];
    }
}
