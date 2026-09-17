<?php

namespace App\Domain\Casos;

use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Models\Caso;

/**
 * El motor de casos. Es el único lugar autorizado a cambiar `casos.estado`.
 * Nadie más debe escribir esa columna directamente.
 */
class CaseStateMachine
{
    public function transicionar(Caso $caso, CasoEstado $hacia, string $actor, array $contexto = []): Caso
    {
        $desde = $caso->estado;

        if (! $desde->puedeTransicionarA($hacia)) {
            throw new TransicionInvalidaException($desde, $hacia);
        }

        if ($hacia === CasoEstado::EnGestion) {
            $alcance = $contexto['alcance'] ?? $this->alcancePorDefecto($caso);

            if ($caso->consentimientoVigente($alcance) === null) {
                throw new ConsentimientoRequeridoException;
            }
        }

        $caso->estado = $hacia;
        $caso->save();

        $caso->registrarEvento($actor, 'estado_cambiado', [
            'desde' => $desde->value,
            'hacia' => $hacia->value,
        ] + $contexto);

        return $caso->refresh();
    }

    public function alcancePorDefecto(Caso $caso): string
    {
        return 'oposicion:'.$caso->consulta->entidad_nombre;
    }
}
