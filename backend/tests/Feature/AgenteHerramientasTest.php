<?php

namespace Tests\Feature;

use App\Domain\Casos\CasoEstado;
use App\Models\AgenteToken;
use App\Models\Caso;
use App\Models\Consentimiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AgenteHerramientasTest extends TestCase
{
    use RefreshDatabase;

    public function test_sin_token_las_herramientas_del_agente_responden_401(): void
    {
        $caso = Caso::factory()->create();

        $this->getJson("/api/agente/casos/{$caso->id}")->assertStatus(401);
    }

    public function test_un_token_de_otro_caso_no_tiene_alcance_aqui(): void
    {
        $caso = Caso::factory()->create();
        $otroCaso = Caso::factory()->create();

        $token = AgenteToken::emitir($otroCaso, 'centinela', ['obtener_caso']);

        $this->withToken($token)
            ->getJson("/api/agente/casos/{$caso->id}")
            ->assertStatus(403);
    }

    public function test_un_token_sin_la_habilidad_requerida_es_rechazado(): void
    {
        $caso = Caso::factory()->create();
        $token = AgenteToken::emitir($caso, 'centinela', ['obtener_caso']);

        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/avanzar-estado", ['estado' => 'notificado'])
            ->assertStatus(403);
    }

    public function test_un_token_expirado_es_rechazado(): void
    {
        $caso = Caso::factory()->create();
        $token = AgenteToken::emitir($caso, 'centinela', ['obtener_caso'], minutos: -1);

        $this->withToken($token)
            ->getJson("/api/agente/casos/{$caso->id}")
            ->assertStatus(401);
    }

    public function test_recorrido_completo_de_las_6_herramientas_con_tokens_con_el_alcance_justo(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Detectado)->create();

        // 1) obtener_caso
        $token = AgenteToken::emitir($caso, 'centinela', ['obtener_caso']);
        $this->withToken($token)->getJson("/api/agente/casos/{$caso->id}")->assertOk();

        // 2) avanzar_estado: detectado -> notificado
        $token = AgenteToken::emitir($caso, 'centinela', ['avanzar_estado']);
        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/avanzar-estado", ['estado' => 'notificado'])
            ->assertOk()
            ->assertJson(['estado' => 'notificado']);

        // 3) notificar_persona
        $token = AgenteToken::emitir($caso, 'centinela', ['notificar_persona']);
        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/notificar-persona", ['plantilla' => 'consulta_no_reconocida'])
            ->assertOk();

        // Persona autoriza directamente en el modelo (fuera del alcance del agente).
        $caso->update(['estado' => CasoEstado::Autorizado]);
        Consentimiento::factory()->create([
            'caso_id' => $caso->id,
            'alcance' => 'oposicion:'.$caso->consulta->entidad_nombre,
        ]);

        // 4) verificar_consentimiento
        $token = AgenteToken::emitir($caso, 'gestor', ['verificar_consentimiento']);
        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/verificar-consentimiento")
            ->assertOk()
            ->assertJson(['vigente' => true]);

        // 5) redactar_oposicion
        $token = AgenteToken::emitir($caso, 'gestor', ['redactar_oposicion']);
        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/redactar-oposicion")
            ->assertStatus(201);

        // 6) registrar_evento
        $token = AgenteToken::emitir($caso, 'gestor', ['registrar_evento']);
        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/eventos", ['tipo' => 'seguimiento_manual'])
            ->assertStatus(201);

        $this->assertDatabaseHas('eventos', ['caso_id' => $caso->id, 'tipo' => 'seguimiento_manual']);
    }

    public function test_redactar_oposicion_falla_sin_consentimiento_vigente(): void
    {
        $caso = Caso::factory()->enEstado(CasoEstado::Autorizado)->create();
        $token = AgenteToken::emitir($caso, 'gestor', ['redactar_oposicion']);

        $this->withToken($token)
            ->postJson("/api/agente/casos/{$caso->id}/redactar-oposicion")
            ->assertStatus(422);
    }
}
