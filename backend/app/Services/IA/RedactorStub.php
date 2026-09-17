<?php

namespace App\Services\IA;

use App\Models\Caso;

/**
 * Stub declarado: se usa mientras no hay ANTHROPIC_API_KEY configurada (ver
 * AIServiceProvider). Genera un texto determinístico con la misma
 * estructura legal que produciría el agente real, para que el resto del
 * recorrido (persistencia, bitácora, transición de estado, frontend) sea
 * end-to-end demostrable sin depender de la API externa.
 *
 * Documentado en AI_USAGE.md: este stub NO es la característica de IA que
 * se declara como "funcional"; se anuncia como tal en el README y aquí.
 */
class RedactorStub implements AgenteRedactorInterface
{
    public function redactarOposicion(Caso $caso): string
    {
        $persona = $caso->persona;
        $consulta = $caso->consulta;
        $contexto = $caso->consentimientos()
            ->whereNotNull('firmado_en')->whereNull('revocado_en')
            ->latest('firmado_en')->value('contexto');

        $parrafoContexto = filled($contexto)
            ? "\n\nEl titular declaró, al momento de autorizar esta gestión, lo siguiente sobre la\nconsulta: \"{$contexto}\". Se cita como hecho relevante para la resolución del caso."
            : '';

        return <<<TEXTO
        [DOCUMENTO GENERADO POR STUB - sin llamada real a Claude API]

        Oposición al tratamiento de datos personales (Art. 12 y 32 LOPDP)

        Entidad: {$consulta->entidad_nombre}
        Caso: {$caso->codigo}
        Titular: {$persona->nombre}

        Por medio del presente, el titular de los datos ejerce su derecho de oposición
        respecto de la consulta registrada el {$consulta->consultada_en->format('d/m/Y')}
        por motivo "{$consulta->motivo}", la cual no reconoce como propia ni autorizada.{$parrafoContexto}

        Se solicita a {$consulta->entidad_nombre} confirmar el origen de dicha consulta,
        rectificar o eliminar el registro si no corresponde a una operación legítima del
        titular, y responder dentro del plazo legal aplicable, conforme a los artículos
        7, 12 y 32 de la Ley Orgánica de Protección de Datos Personales del Ecuador.

        Documento generado automáticamente por el agente Gestor de Decision Data.
        TEXTO;
    }
}
