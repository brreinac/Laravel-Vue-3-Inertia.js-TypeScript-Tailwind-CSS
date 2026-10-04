<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['body' => ['required', 'string', 'min:1', 'max:5000']];
    }

    public function messages(): array
    {
        return ['body.required' => 'El comentario no puede estar vacío.', 'body.max' => 'El comentario no puede superar los :max caracteres.'];
    }
}
