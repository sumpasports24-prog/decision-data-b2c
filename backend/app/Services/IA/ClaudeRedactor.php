<?php

namespace App\Services\IA;

use App\Models\Caso;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Redactor real: una llamada a la Claude API (Messages API) para generar el
 * texto de la oposición. Se activa automáticamente cuando ANTHROPIC_API_KEY
 * está presente (ver AIServiceProvider); si falla, el llamador decide si
 * cae a RedactorStub o propaga el error.
 */
class ClaudeRedactor implements AgenteRedactorInterface
{
    public function redactarOposicion(Caso $caso): string
    {
        $persona = $caso->persona;
        $consulta = $caso->consulta;

        $prompt = <<<PROMPT
        Redacta, en español formal ecuatoriano, una carta de oposición al tratamiento
        de datos personales bajo la LOPDP de Ecuador (artículos 7, 12 y 32), dirigida a
        la entidad "{$consulta->entidad_nombre}", en nombre del titular "{$persona->nombre}"
        (caso {$caso->codigo}). El titular no reconoce una consulta realizada el
        {$consulta->consultada_en->format('d/m/Y')} por motivo "{$consulta->motivo}".
        Pide confirmar el origen, rectificar o eliminar el registro si no corresponde,
        y fijar un plazo de respuesta. Devuelve únicamente el texto del documento, sin
        explicaciones adicionales.
        PROMPT;

        $respuesta = Http::withHeaders([
            'x-api-key' => config('services.anthropic.key'),
            'anthropic-version' => '2023-06-01',
        ])
            ->baseUrl('https://api.anthropic.com/v1')
            ->timeout(30)
            ->post('/messages', [
                'model' => config('services.anthropic.model', 'claude-sonnet-4-5'),
                'max_tokens' => 1024,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);

        if ($respuesta->failed()) {
            throw new RuntimeException('Claude API falló al redactar la oposición: '.$respuesta->body());
        }

        return data_get($respuesta->json(), 'content.0.text', '');
    }
}
