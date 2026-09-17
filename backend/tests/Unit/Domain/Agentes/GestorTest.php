<?php

namespace Tests\Unit\Domain\Agentes;

use App\Domain\Casos\Agentes\Gestor;
use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Models\Caso;
use App\Models\Consentimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GestorTest extends TestCase
{
    use RefreshDatabase;

    public function test_redacta_la_oposicion_y_avanza_el_caso_a_en_gestion(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();
        $alcance = (new CaseStateMachine)->alcancePorDefecto($caso);

        Consentimiento::factory()->create(['caso_id' => $caso->id, 'alcance' => $alcance]);

        $documento = app(Gestor::class)->gestionar($caso);

        $this->assertSame('oposicion_lopdp', $documento->tipo);
        $this->assertNotEmpty($documento->contenido);
        $this->assertSame(CasoEstado::EnGestion, $caso->refresh()->estado);
        $this->assertDatabaseHas('eventos', ['caso_id' => $caso->id, 'tipo' => 'oposicion_generada']);
    }

    public function test_no_gestiona_un_caso_sin_consentimiento_vigente(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();

        $this->expectException(ConsentimientoRequeridoException::class);

        app(Gestor::class)->gestionar($caso);
    }
}
