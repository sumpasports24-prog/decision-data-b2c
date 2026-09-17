<?php

namespace App\Domain\Casos;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Reloj de plazos legales del motor de casos: cuenta días hábiles (sin
 * sábados, domingos ni feriados configurados) para calcular vencimientos y
 * detectar si un caso ya venció.
 */
class DeadlineClock
{
    private array $feriados;

    public function __construct(?array $feriados = null)
    {
        $this->feriados = $feriados ?? config('casos.feriados', []);
    }

    public function esDiaHabil(CarbonInterface $fecha): bool
    {
        if ($fecha->isWeekend()) {
            return false;
        }

        return ! in_array($fecha->toDateString(), $this->feriados, true);
    }

    public function sumarDiasHabiles(CarbonInterface $desde, int $dias): Carbon
    {
        $cursor = Carbon::instance($desde);
        $restantes = $dias;

        while ($restantes > 0) {
            $cursor = $cursor->copy()->addDay();

            if ($this->esDiaHabil($cursor)) {
                $restantes--;
            }
        }

        return $cursor;
    }

    public function calcularVencimiento(CarbonInterface $abiertoEn): Carbon
    {
        return $this->sumarDiasHabiles($abiertoEn, config('casos.dias_habiles_respuesta', 15));
    }

    public function estaVencido(?CarbonInterface $venceEn, ?CarbonInterface $ahora = null): bool
    {
        if ($venceEn === null) {
            return false;
        }

        $ahora ??= Carbon::now();

        return $ahora->greaterThan($venceEn);
    }
}
