<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsultaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'entidad_nombre' => $this->entidad_nombre,
            'motivo' => $this->motivo,
            'consultada_en' => $this->consultada_en,
            'reconocida' => $this->reconocida,
        ];
    }
}
