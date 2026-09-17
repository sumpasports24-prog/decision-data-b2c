<?php

namespace Tests\Unit\Domain;

use App\Domain\Casos\DeadlineClock;
use Carbon\Carbon;
use Tests\TestCase;

class DeadlineClockTest extends TestCase
{
    public function test_no_considera_habil_un_sabado_ni_un_domingo(): void
    {
        $reloj = new DeadlineClock(feriados: []);

        $this->assertFalse($reloj->esDiaHabil(Carbon::parse('2026-09-19'))); // sábado
        $this->assertFalse($reloj->esDiaHabil(Carbon::parse('2026-09-20'))); // domingo
        $this->assertTrue($reloj->esDiaHabil(Carbon::parse('2026-09-17'))); // jueves
    }

    public function test_no_considera_habil_un_feriado_configurado(): void
    {
        $reloj = new DeadlineClock(feriados: ['2026-05-01']);

        $this->assertFalse($reloj->esDiaHabil(Carbon::parse('2026-05-01')));
    }

    public function test_suma_dias_habiles_saltando_fin_de_semana(): void
    {
        $reloj = new DeadlineClock(feriados: []);

        // Jueves 2026-09-17 + 3 días hábiles: vie 18, (sáb/dom saltan), lun 21, mar 22.
        $resultado = $reloj->sumarDiasHabiles(Carbon::parse('2026-09-17'), 3);

        $this->assertSame('2026-09-22', $resultado->toDateString());
    }

    public function test_detecta_vencimiento_cuando_ya_paso_la_fecha_limite(): void
    {
        $reloj = new DeadlineClock;

        $venceEn = Carbon::parse('2026-09-10');
        $ahora = Carbon::parse('2026-09-17');

        $this->assertTrue($reloj->estaVencido($venceEn, $ahora));
        $this->assertFalse($reloj->estaVencido(Carbon::parse('2026-09-20'), $ahora));
    }

    public function test_un_caso_sin_fecha_de_vencimiento_nunca_se_reporta_vencido(): void
    {
        $reloj = new DeadlineClock;

        $this->assertFalse($reloj->estaVencido(null));
    }
}
