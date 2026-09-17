<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Envuelve el array plano que arma PanoramaService::resumenPara() (no es un
 * modelo Eloquent) en la forma que espera el frontend.
 */
class PanoramaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'persona' => new PersonaResource($this->resource['persona']),
            'score' => $this->resource['score'],
            'huella_de_consulta' => ConsultaResource::collection($this->resource['huella_de_consulta']),
            'casos' => CasoResumenResource::collection($this->resource['casos']),
            'alertas' => CasoResumenResource::collection($this->resource['alertas']),
        ];
    }
}
