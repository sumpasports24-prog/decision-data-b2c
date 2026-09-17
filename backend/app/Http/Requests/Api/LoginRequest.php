<?php

namespace App\Http\Requests\Api;

use App\Rules\CedulaEcuatoriana;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cedula' => ['required', 'string', new CedulaEcuatoriana],
        ];
    }
}
