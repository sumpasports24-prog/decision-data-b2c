<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caso_id' => $this->caso_id,
            'actor' => $this->actor,
            'tipo' => $this->tipo,
            'carga' => $this->carga,
            'ocurrio_en' => $this->ocurrio_en,
        ];
    }
}
