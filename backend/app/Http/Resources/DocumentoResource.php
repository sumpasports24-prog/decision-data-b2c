<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caso_id' => $this->caso_id,
            'tipo' => $this->tipo,
            'contenido' => $this->contenido,
            'generado_por' => $this->generado_por,
            'creado_en' => $this->creado_en,
        ];
    }
}
