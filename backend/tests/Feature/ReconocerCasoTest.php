<?php

namespace Tests\Feature;

use App\Domain\Casos\CasoEstado;
use App\Models\Caso;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El camino corto: la persona reconoce la consulta ("sí, fui yo") y el caso
 * se cierra sin pasar por consentimiento ni Gestor. Es el camino que toma
 * la mayoría de las consultas — el de oposición es la excepción.
 */
class ReconocerCasoTest extends TestCase
{
    use RefreshDatabase;

    public function test_reconocer_una_consulta_cierra_el_caso_como_descartado(): void
    {
        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create(['persona_id' => $persona->id]);

        $respuesta = $this->actingAs($persona, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/reconocer");

        $respuesta->assertOk()->assertJsonPath('estado', 'descartado');
        $this->assertSame(CasoEstado::Descartado, $caso->refresh()->estado);
    }

    public function test_reconocer_marca_la_consulta_como_reconocida(): void
    {
        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create(['persona_id' => $persona->id]);

        $this->actingAs($persona, 'sanctum')->postJson("/api/casos/{$caso->id}/reconocer");

        $this->assertTrue($caso->consulta->fresh()->reconocida);
        $this->assertDatabaseHas('eventos', ['caso_id' => $caso->id, 'tipo' => 'consulta_reconocida']);
    }

    public function test_no_se_puede_reconocer_un_caso_que_no_esta_notificado(): void
    {
        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::EnGestion)->create(['persona_id' => $persona->id]);

        $this->actingAs($persona, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/reconocer")
            ->assertStatus(409);
    }

    public function test_una_persona_no_puede_reconocer_el_caso_de_otra(): void
    {
        $intruso = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create();

        $this->actingAs($intruso, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/reconocer")
            ->assertStatus(403);

        $this->assertSame(CasoEstado::Notificado, $caso->refresh()->estado);
    }
}
