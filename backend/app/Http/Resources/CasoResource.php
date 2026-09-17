<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Forma completa de un caso: detalle + bitácora + documentos + consentimientos. */
class CasoResource extends JsonResource
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
            'persona' => $this->whenLoaded('persona', fn () => new PersonaResource($this->persona)),
            'consulta' => $this->whenLoaded('consulta', fn () => new ConsultaResource($this->consulta)),
            'eventos' => EventoResource::collection($this->whenLoaded('eventos')),
            'documentos' => DocumentoResource::collection($this->whenLoaded('documentos')),
            'consentimientos' => ConsentimientoResource::collection($this->whenLoaded('consentimientos')),
        ];
    }
}
