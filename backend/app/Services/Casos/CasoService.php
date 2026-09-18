<?php

namespace App\Services\Casos;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\CasoNoEsperaAutorizacionException;
use App\Models\Caso;

/**
 * El otro lado de la moneda de ConsentimientoService: qué pasa cuando la
 * persona SÍ reconoce la consulta. Es el camino más frecuente — la mayoría
 * de las consultas se reconocen — y por eso se cierra en un solo paso, sin
 * consentimiento ni Gestor de por medio: no hay nada que gestionar.
 */
class CasoService
{
    public function __construct(private readonly CaseStateMachine $motor) {}

    public function reconocer(Caso $caso): Caso
    {
        if ($caso->estado !== CasoEstado::Notificado) {
            throw new CasoNoEsperaAutorizacionException;
        }

        $caso->consulta->update(['reconocida' => true]);

        $caso->registrarEvento('persona', 'consulta_reconocida', [
            'consulta_id' => $caso->consulta_id,
        ]);

        return $this->motor->transicionar($caso, CasoEstado::Descartado, actor: 'persona');
    }
}
