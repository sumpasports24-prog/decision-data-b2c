<?php

namespace Tests\Unit\Domain;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\DeadlineClock;
use App\Domain\Casos\EscaladorDePlazos;
use App\Models\Caso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EscaladorDePlazosTest extends TestCase
{
    use RefreshDatabase;

    public function test_escala_automaticamente_un_caso_en_gestion_con_plazo_vencido(): void
    {
        $vencido = Caso::factory()->enEstado(CasoEstado::EnGestion)->create([
            'vence_en' => now()->subDay(),
        ]);

        $escalador = new EscaladorDePlazos(new CaseStateMachine, new DeadlineClock);
        $escalados = $escalador->ejecutar();

        $this->assertCount(1, $escalados);
        $this->assertSame(CasoEstado::Escalado, $vencido->refresh()->estado);
        $this->assertDatabaseHas('eventos', [
            'caso_id' => $vencido->id,
            'tipo' => 'estado_cambiado',
        ]);
    }

    public function test_no_toca_un_caso_en_gestion_que_todavia_esta_dentro_del_plazo(): void
    {
        $vigente = Caso::factory()->enEstado(CasoEstado::EnGestion)->create([
            'vence_en' => now()->addDays(3),
        ]);

        $escalador = new EscaladorDePlazos(new CaseStateMachine, new DeadlineClock);
        $escalados = $escalador->ejecutar();

        $this->assertCount(0, $escalados);
        $this->assertSame(CasoEstado::EnGestion, $vigente->refresh()->estado);
    }

    public function test_no_toca_casos_que_no_estan_en_gestion(): void
    {
        $resuelto = Caso::factory()->enEstado(CasoEstado::Resuelto)->create([
            'vence_en' => now()->subDay(),
        ]);

        $escalador = new EscaladorDePlazos(new CaseStateMachine, new DeadlineClock);
        $escalados = $escalador->ejecutar();

        $this->assertCount(0, $escalados);
        $this->assertSame(CasoEstado::Resuelto, $resuelto->refresh()->estado);
    }
}
