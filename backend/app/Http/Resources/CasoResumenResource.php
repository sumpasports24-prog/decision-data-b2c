<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Forma reducida de un caso para listas (Panorama, Mis casos): sin bitácora ni documentos. */
class CasoResumenResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'tipo' => $this->tipo,
            'estado' => $this->estado->value,
            'abierto_en' => $this->abierto_en,
            'vence_en' => $this->vence_en,
            'consulta' => $this->whenLoaded('consulta', fn () => new ConsultaResource($this->consulta)),
        ];
    }
}
