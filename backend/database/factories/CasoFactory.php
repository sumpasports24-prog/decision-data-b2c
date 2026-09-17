<?php

namespace Database\Factories;

use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\DeadlineClock;
use App\Models\Caso;
use App\Models\Consulta;
use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

class CasoFactory extends Factory
{
    protected $model = Caso::class;

    public function definition(): array
    {
        $abiertoEn = now();

        return [
            'persona_id' => Persona::factory(),
            'consulta_id' => Consulta::factory(),
            'tipo' => Caso::TIPO_CONSULTA_NO_RECONOCIDA,
            'estado' => CasoEstado::Detectado,
            'abierto_en' => $abiertoEn,
            'vence_en' => app(DeadlineClock::class)->calcularVencimiento($abiertoEn),
        ];
    }

    public function enEstado(CasoEstado $estado): static
    {
        return $this->state(fn () => ['estado' => $estado]);
    }
}
