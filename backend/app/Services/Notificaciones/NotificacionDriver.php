<?php

namespace App\Services\Notificaciones;

use App\Models\Persona;

interface NotificacionDriver
{
    /**
     * @return array{proveedor_id: ?string, enviado: bool}
     */
    public function enviar(Persona $persona, string $plantilla, array $variables = []): array;
}
