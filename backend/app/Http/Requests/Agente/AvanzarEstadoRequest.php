<?php

namespace App\Http\Requests\Agente;

use App\Domain\Casos\CasoEstado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AvanzarEstadoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // el middleware VerificarTokenAgente ya validó el alcance del token
    }

    public function rules(): array
    {
        return [
            'estado' => ['required', new Enum(CasoEstado::class)],
            'contexto' => ['nullable', 'array'],
        ];
    }

    public function estado(): CasoEstado
    {
        return CasoEstado::from($this->validated('estado'));
    }
}
