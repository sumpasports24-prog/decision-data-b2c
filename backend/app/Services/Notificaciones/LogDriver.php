<?php

namespace App\Services\Notificaciones;

use App\Models\Persona;
use Illuminate\Support\Facades\Log;

/**
 * Driver por defecto para desarrollo y demo: no depende de credenciales de
 * Meta. Permite correr el recorrido completo (incluida la notificación) sin
 * ningún servicio externo, tal como exige el enunciado.
 */
class LogDriver implements NotificacionDriver
{
    public function enviar(Persona $persona, string $plantilla, array $variables = []): array
    {
        $mensaje = MensajeNotificacion::renderizar($plantilla, $variables);

        Log::channel(config('logging.default'))->info('[notificacion:log] '.$plantilla, [
            'persona_id' => $persona->id,
            'telefono' => $persona->telefono_e164,
            'mensaje' => $mensaje,
        ]);

        return ['proveedor_id' => null, 'enviado' => true, 'mensaje' => $mensaje];
    }
}
