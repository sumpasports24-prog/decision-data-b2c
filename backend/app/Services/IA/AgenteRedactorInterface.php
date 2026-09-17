<?php

namespace App\Services\IA;

use App\Models\Caso;

interface AgenteRedactorInterface
{
    /**
     * Redacta el texto de la oposición LOPDP para un caso. Devuelve solo el
     * contenido del documento; el Gestor decide dónde y cómo persistirlo.
     */
    public function redactarOposicion(Caso $caso): string;
}
