<?php

namespace App\Http\Requests\Agente;

use Illuminate\Foundation\Http\FormRequest;

class RegistrarEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware VerificarTokenAgente ya validó el alcance del token
    }

    public function rules(): array
    {
        return [
            'tipo' => ['required', 'string', 'max:100'],
            'carga' => ['nullable', 'array'],
        ];
    }
}
