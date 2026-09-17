<?php

namespace Tests\Feature;

use App\Domain\Casos\CasoEstado;
use App\Jobs\GestionarCasoJob;
use App\Models\Caso;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class ConsentimientoFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_por_cedula_emite_token_para_una_persona_sembrada(): void
    {
        $cedula = '1710034065';
        $persona = Persona::factory()->create(['cedula_hash' => Persona::hashCedula($cedula)]);

        $respuesta = $this->postJson('/api/auth/login', ['cedula' => $cedula]);

        $respuesta->assertOk()->assertJsonStructure(['token', 'persona' => ['id', 'nombre']]);
        $this->assertSame($persona->id, $respuesta->json('persona.id'));
    }

    public function test_login_rechaza_una_cedula_mal_formada_con_mensaje_especifico(): void
    {
        $this->postJson('/api/auth/login', ['cedula' => '123'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('cedula');
    }

    public function test_login_con_cedula_valida_pero_desconocida_responde_404(): void
    {
        $this->postJson('/api/auth/login', ['cedula' => '1710034065'])->assertStatus(404);
    }

    public function test_firmar_consentimiento_autoriza_el_caso_y_encola_la_gestion(): void
    {
        // No depende del QUEUE_CONNECTION real del entorno (sync en phpunit.xml,
        // pero redis dentro de Docker): se verifica que el job se encola con el
        // caso correcto, sin acoplarse a si se procesa en el mismo request o no.
        Bus::fake();

        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create(['persona_id' => $persona->id]);

        $respuesta = $this->actingAs($persona, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/consentimiento");

        $respuesta->assertStatus(201);
        $this->assertSame(CasoEstado::Autorizado, $caso->refresh()->estado);
        Bus::assertDispatched(GestionarCasoJob::class, fn ($job) => $job->caso->is($caso));
    }

    // Qué hace el job cuando se procesa de verdad (Gestor::gestionar) ya está
    // cubierto por tests/Unit/Domain/Agentes/GestorTest.php.

    public function test_firmar_consentimiento_guarda_el_contexto_que_escribe_la_persona(): void
    {
        Bus::fake();

        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create(['persona_id' => $persona->id]);

        $this->actingAs($persona, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/consentimiento", [
                'contexto' => 'Yo nunca estuve en Cuenca, ni he pedido crédito en esa cooperativa.',
            ])
            ->assertStatus(201)
            ->assertJsonPath('contexto', 'Yo nunca estuve en Cuenca, ni he pedido crédito en esa cooperativa.');

        $this->assertDatabaseHas('consentimientos', [
            'caso_id' => $caso->id,
            'contexto' => 'Yo nunca estuve en Cuenca, ni he pedido crédito en esa cooperativa.',
        ]);
    }

    public function test_firmar_consentimiento_sin_contexto_no_falla(): void
    {
        Bus::fake();

        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::Notificado)->create(['persona_id' => $persona->id]);

        $this->actingAs($persona, 'sanctum')
            ->postJson("/api/casos/{$caso->id}/consentimiento")
            ->assertStatus(201)
            ->assertJsonPath('contexto', null);
    }

    public function test_una_persona_no_puede_leer_el_caso_de_otra(): void
    {
        $intruso = Persona::factory()->create();
        $caso = Caso::factory()->create();

        $this->actingAs($intruso, 'sanctum')
            ->getJson("/api/casos/{$caso->id}")
            ->assertStatus(403);
    }

    public function test_revocar_un_consentimiento_es_idempotente(): void
    {
        $persona = Persona::factory()->create();
        $caso = Caso::factory()->enEstado(CasoEstado::EnGestion)->create(['persona_id' => $persona->id]);
        $consentimiento = \App\Models\Consentimiento::factory()->create(['caso_id' => $caso->id]);

        $this->actingAs($persona, 'sanctum')
            ->postJson("/api/consentimientos/{$consentimiento->id}/revocar")
            ->assertOk();

        $this->assertNotNull($consentimiento->refresh()->revocado_en);

        // Segunda llamada: no debe fallar ni duplicar el evento.
        $this->actingAs($persona, 'sanctum')
            ->postJson("/api/consentimientos/{$consentimiento->id}/revocar")
            ->assertOk();

        $this->assertSame(1, \App\Models\Evento::where('caso_id', $caso->id)
            ->where('tipo', 'consentimiento_revocado')->count());
    }
}
