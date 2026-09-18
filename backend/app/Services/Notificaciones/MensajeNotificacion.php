<?php

namespace App\Services\Notificaciones;

/**
 * Renderiza el texto humano de cada plantilla de WhatsApp a partir de sus
 * variables posicionales. Con Meta Cloud API real, Meta renderiza la
 * plantilla aprobada del lado de ellos con estas mismas variables; esto es
 * la versión propia para poder mostrar el mensaje exacto en la bitácora del
 * caso (con NOTIFICATION_DRIVER=log) y para lo que de verdad se le manda a
 * Meta como `body` cuando el driver es `whatsapp`.
 */
class MensajeNotificacion
{
    private const PLANTILLAS = [
        'consulta_no_reconocida' => 'Hola {0}, *{1}* consultó tu historial crediticio. Entra a Panorama y confirma si la '
            .'reconoces — si no fuiste tú, presentamos una oposición formal en tu nombre bajo la LOPDP. Caso {2}.',
        'oposicion_enviada' => 'Listo {0}. La oposición ante *{1}* fue emitida y quedó registrada como evidencia. '
            .'Te aviso apenas haya respuesta o si vence el plazo. Caso {2}.',
    ];

    public static function renderizar(string $plantilla, array $variables): string
    {
        $texto = self::PLANTILLAS[$plantilla] ?? implode(' · ', $variables);

        foreach (array_values($variables) as $i => $valor) {
            $texto = str_replace('{'.$i.'}', (string) $valor, $texto);
        }

        return $texto;
    }
}
