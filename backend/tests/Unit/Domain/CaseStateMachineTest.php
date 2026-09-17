<?php

namespace Tests\Unit\Domain;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Models\Caso;
use App\Models\Consentimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseStateMachineTest extends TestCase
{
    use RefreshDatabase;

    private CaseStateMachine $motor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->motor = new CaseStateMachine;
    }

    public function test_rechaza_una_transicion_fuera_del_grafo_de_estados(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Detectado)->create();

        $this->expectException(TransicionInvalidaException::class);

        // detectado -> en_gestion salta pasos obligatorios: no es válido.
        $this->motor->transicionar($caso, CasoEstado::EnGestion, actor: 'test');
    }

    public function test_rechaza_avanzar_a_resuelto_desde_notificado(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create();

        $this->expectException(TransicionInvalidaException::class);

        $this->motor->transicionar($caso, CasoEstado::Resuelto, actor: 'test');
    }

    public function test_no_permite_avanzar_a_en_gestion_sin_consentimiento_vigente(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();

        $this->expectException(ConsentimientoRequeridoException::class);

        $this->motor->transicionar($caso, CasoEstado::EnGestion, actor: 'gestor');
    }

    public function test_no_permite_avanzar_a_en_gestion_con_consentimiento_revocado(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();
        $alcance = $this->motor->alcancePorDefecto($caso);

        Consentimiento::factory()->revocado()->create([
            'caso_id' => $caso->id,
            'alcance' => $alcance,
        ]);

        $this->expectException(ConsentimientoRequeridoException::class);

        $this->motor->transicionar($caso, CasoEstado::EnGestion, actor: 'gestor');
    }

    public function test_permite_avanzar_a_en_gestion_con_consentimiento_firmado_y_vigente(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();
        $alcance = $this->motor->alcancePorDefecto($caso);

        Consentimiento::factory()->create([
            'caso_id' => $caso->id,
            'alcance' => $alcance,
        ]);

        $resultado = $this->motor->transicionar($caso, CasoEstado::EnGestion, actor: 'gestor');

        $this->assertSame(CasoEstado::EnGestion, $resultado->estado);
        $this->assertDatabaseHas('eventos', [
            'caso_id' => $caso->id,
            'tipo' => 'estado_cambiado',
        ]);
    }
}
