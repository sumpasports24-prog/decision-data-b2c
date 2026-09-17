<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class FirmarConsentimientoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('autorizar', $this->route('caso'));
    }

    public function rules(): array
    {
        return [];
    }
}
