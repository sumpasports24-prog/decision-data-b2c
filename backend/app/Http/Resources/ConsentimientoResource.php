<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsentimientoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'caso_id' => $this->caso_id,
            'alcance' => $this->alcance,
            'texto_version' => $this->texto_version,
            'contexto' => $this->contexto,
            'firmado_en' => $this->firmado_en,
            'revocado_en' => $this->revocado_en,
            'canal' => $this->canal,
        ];
    }
}
