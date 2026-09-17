<?php

namespace App\Services\Agente;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Models\Caso;
use App\Models\Consentimiento;
use App\Models\Documento;
use App\Models\Evento;
use App\Services\IA\AgenteRedactorInterface;
use App\Services\Notificaciones\NotificacionDriver;

/**
 * Implementa las 6 herramientas del agente (contrato, sección 12 del
 * brief). Cada método corresponde uno a uno con un endpoint de
 * AgenteHerramientasController; el controlador solo valida y traduce
 * excepciones a códigos HTTP, toda la lógica vive aquí.
 */
class HerramientasAgenteService
{
    public function __construct(
        private readonly CaseStateMachine $motor,
        private readonly AgenteRedactorInterface $redactor,
        private readonly NotificacionDriver $notificaciones,
    ) {}

    public function obtenerCaso(Caso $caso): Caso
    {
        return $caso->load(['persona', 'consulta.entidad', 'eventos', 'documentos', 'consentimientos']);
    }

    /**
     * @return array{alcance: string, consentimiento: ?Consentimiento, vigente: bool}
     */
    public function verificarConsentimiento(Caso $caso, ?string $alcance): array
    {
        $alcance ??= $this->motor->alcancePorDefecto($caso);
        $consentimiento = $caso->consentimientoVigente($alcance);

        return ['alcance' => $alcance, 'consentimiento' => $consentimiento, 'vigente' => $consentimiento !== null];
    }

    public function redactarOposicion(Caso $caso, string $actor): Documento
    {
        $alcance = $this->motor->alcancePorDefecto($caso);

        if ($caso->consentimientoVigente($alcance) === null) {
            throw new ConsentimientoRequeridoException;
        }

        $documento = $caso->documentos()->create([
            'tipo' => 'oposicion_lopdp',
            'contenido' => $this->redactor->redactarOposicion($caso),
            'generado_por' => $actor,
        ]);

        $caso->registrarEvento($actor, 'oposicion_generada', ['documento_id' => $documento->id]);

        return $documento;
    }

    public function registrarEvento(Caso $caso, string $actor, string $tipo, array $carga): Evento
    {
        return $caso->registrarEvento($actor, $tipo, $carga);
    }

    public function avanzarEstado(Caso $caso, CasoEstado $hacia, string $actor, array $contexto): Caso
    {
        return $this->motor->transicionar($caso, $hacia, actor: $actor, contexto: $contexto);
    }

    public function notificarPersona(Caso $caso, string $actor, string $plantilla, array $variables): array
    {
        $resultado = $this->notificaciones->enviar($caso->persona, $plantilla, $variables);

        $caso->registrarEvento($actor, 'notificacion_enviada', $resultado);

        return $resultado;
    }
}
