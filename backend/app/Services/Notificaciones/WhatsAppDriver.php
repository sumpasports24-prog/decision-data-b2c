<?php

namespace App\Services\Notificaciones;

use App\Models\Persona;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Envía la plantilla de utilidad vía Meta Cloud API (WhatsApp Business).
 * Requiere WHATSAPP_TOKEN y WHATSAPP_PHONE_NUMBER_ID configurados; si la
 * plantilla aún no está aprobada por Meta, usar NOTIFICATION_DRIVER=log.
 */
class WhatsAppDriver implements NotificacionDriver
{
    public function enviar(Persona $persona, string $plantilla, array $variables = []): array
    {
        $telefono = ltrim($persona->telefono_e164, '+');

        $respuesta = Http::withToken(config('services.whatsapp.token'))
            ->baseUrl('https://graph.facebook.com/v20.0')
            ->post('/'.config('services.whatsapp.phone_number_id').'/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $telefono,
                'type' => 'template',
                'template' => [
                    'name' => $plantilla,
                    'language' => ['code' => 'es'],
                    'components' => [[
                        'type' => 'body',
                        'parameters' => collect($variables)
                            ->map(fn ($valor) => ['type' => 'text', 'text' => (string) $valor])
                            ->values()
                            ->all(),
                    ]],
                ],
            ]);

        if ($respuesta->failed()) {
            Log::warning('[notificacion:whatsapp] fallo al enviar', [
                'persona_id' => $persona->id,
                'status' => $respuesta->status(),
                'body' => $respuesta->body(),
            ]);

            return ['proveedor_id' => null, 'enviado' => false];
        }

        return ['proveedor_id' => data_get($respuesta->json(), 'messages.0.id'), 'enviado' => true];
    }
}
