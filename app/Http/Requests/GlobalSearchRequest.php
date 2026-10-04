<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GlobalSearchRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['q' => ['required', 'string', 'min:2', 'max:100']];
    }

    public function messages(): array
    {
        return ['q.required' => 'Ingrese un término de búsqueda.', 'q.min' => 'Ingrese al menos :min caracteres para buscar.'];
    }
}
