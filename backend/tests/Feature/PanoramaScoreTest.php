<?php

namespace Tests\Feature;

use App\Models\Consulta;
use App\Models\Entidad;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanoramaScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_score_viene_con_su_desglose_de_factores(): void
    {
        $persona = Persona::factory()->create();

        $this->actingAs($persona, 'sanctum')
            ->getJson('/api/panorama')
            ->assertOk()
            ->assertJsonStructure([
                'score' => [
                    'total',
                    'factores' => [
                        '*' => ['clave', 'etiqueta', 'peso', 'direccion', 'detalle'],
                    ],
                ],
            ]);
    }

    public function test_las_consultas_no_reconocidas_marcan_el_factor_correspondiente_en_negativo(): void
    {
        $persona = Persona::factory()->create();
        $entidad = Entidad::factory()->create();
        Consulta::factory()->create(['persona_id' => $persona->id, 'entidad_id' => $entidad->id, 'reconocida' => false]);

        $respuesta = $this->actingAs($persona, 'sanctum')->getJson('/api/panorama')->json();

        $factor = collect($respuesta['score']['factores'])->firstWhere('clave', 'endeudamiento');

        $this->assertSame('negativo', $factor['direccion']);
    }
}
