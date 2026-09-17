<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RevocarConsentimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('revocar', $this->route('consentimiento'));
    }

    public function rules(): array
    {
        return [];
    }
}
