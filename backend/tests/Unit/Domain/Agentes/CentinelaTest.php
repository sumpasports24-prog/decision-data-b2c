<?php

namespace Tests\Unit\Domain\Agentes;

use App\Domain\Casos\Agentes\Centinela;
use App\Domain\Casos\CasoEstado;
use App\Models\Caso;
use App\Models\Consulta;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CentinelaTest extends TestCase
{
    use RefreshDatabase;

    public function test_abre_un_caso_para_cada_consulta_sin_revisar_y_sin_caso(): void
    {
        $persona = Persona::factory()->create();
        $consulta = Consulta::factory()->create(['persona_id' => $persona->id, 'reconocida' => null]);
        Consulta::factory()->create(['persona_id' => $persona->id, 'reconocida' => true]);

        $casos = app(Centinela::class)->detectar();

        $this->assertCount(1, $casos);

        $caso = $casos->first();
        $this->assertSame($consulta->id, $caso->consulta_id);
        $this->assertSame(CasoEstado::Notificado, $caso->estado);
        $this->assertNotNull($caso->vence_en);
        $this->assertDatabaseHas('eventos', ['caso_id' => $caso->id, 'tipo' => 'caso_creado']);
        $this->assertDatabaseHas('eventos', ['caso_id' => $caso->id, 'tipo' => 'notificacion_enviada']);
    }

    public function test_no_abre_dos_veces_el_mismo_caso_para_una_consulta_ya_atendida(): void
    {
        $persona = Persona::factory()->create();
        Consulta::factory()->create(['persona_id' => $persona->id, 'reconocida' => null]);

        $centinela = app(Centinela::class);
        $primeraCorrida = $centinela->detectar();
        $segundaCorrida = $centinela->detectar();

        $this->assertCount(1, $primeraCorrida);
        $this->assertCount(0, $segundaCorrida);
    }

    public function test_no_reabre_un_caso_para_una_consulta_ya_disputada_sin_relacion_de_caso_cargada(): void
    {
        // Reconstruye el escenario real: la persona ya dijo "no la reconozco"
        // (reconocida=false), lo que solo pasa cuando ya existe un caso. El
        // Centinela no debe tocarla igual, aunque ya no filtre por false.
        $persona = Persona::factory()->create();
        $consulta = Consulta::factory()->noReconocida()->create(['persona_id' => $persona->id]);
        Caso::factory()->create(['persona_id' => $persona->id, 'consulta_id' => $consulta->id]);

        $casos = app(Centinela::class)->detectar();

        $this->assertCount(0, $casos);
    }
}
