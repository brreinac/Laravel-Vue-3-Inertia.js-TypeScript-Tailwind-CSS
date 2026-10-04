<?php

namespace App\Http\Requests;

use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeTaskStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => ['required', Rule::in(TaskStatus::values())]];
    }

    public function messages(): array
    {
        return ['status.required' => 'Debe seleccionar un estado.', 'status.in' => 'El estado de la tarea no es válido.'];
    }
}
