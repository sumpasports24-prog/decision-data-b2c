<?php

namespace App\Http\Requests\Api;

use App\Models\Caso;
use Illuminate\Foundation\Http\FormRequest;

class VerCasoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('view', $this->route('caso'));
    }

    public function rules(): array
    {
        return [];
    }
}
