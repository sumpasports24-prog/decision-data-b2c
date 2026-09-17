<?php

namespace App\Http\Requests\Agente;

use Illuminate\Foundation\Http\FormRequest;

class NotificarPersonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware VerificarTokenAgente ya validó el alcance del token
    }

    public function rules(): array
    {
        return [
            'plantilla' => ['required', 'string', 'max:100'],
            'variables' => ['nullable', 'array'],
        ];
    }
}
