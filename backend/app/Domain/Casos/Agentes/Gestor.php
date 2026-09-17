<?php

namespace App\Domain\Casos\Agentes;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Models\Caso;
use App\Models\Documento;
use App\Services\IA\AgenteRedactorInterface;
use App\Services\Notificaciones\NotificacionDriver;

/**
 * Redacta la oposición, la dirige a la entidad y la registra como evidencia.
 * Solo actúa si existe consentimiento firmado y vigente para el alcance del
 * caso: es el propio CaseStateMachine quien lo exige al avanzar a
 * `en_gestion`, así que este agente nunca puede saltarse la regla aunque
 * tenga un bug.
 */
class Gestor
{
    public function __construct(
        private readonly CaseStateMachine $motor,
        private readonly AgenteRedactorInterface $redactor,
        private readonly NotificacionDriver $notificaciones,
    ) {}

    public function gestionar(Caso $caso): Documento
    {
        $alcance = $this->motor->alcancePorDefecto($caso);

        if ($caso->consentimientoVigente($alcance) === null) {
            throw new ConsentimientoRequeridoException;
        }

        $texto = $this->redactor->redactarOposicion($caso);

        $documento = $caso->documentos()->create([
            'tipo' => 'oposicion_lopdp',
            'contenido' => $texto,
            'generado_por' => 'gestor',
        ]);

        $caso->registrarEvento('gestor', 'oposicion_generada', [
            'documento_id' => $documento->id,
        ]);

        $this->motor->transicionar($caso, CasoEstado::EnGestion, actor: 'gestor', contexto: [
            'alcance' => $alcance,
        ]);

        $resultado = $this->notificaciones->enviar(
            $caso->persona,
            plantilla: 'oposicion_enviada',
            variables: [$caso->persona->nombre, $caso->consulta->entidad_nombre, $caso->codigo],
        );

        $caso->registrarEvento('gestor', 'notificacion_enviada', $resultado);

        return $documento;
    }
}
