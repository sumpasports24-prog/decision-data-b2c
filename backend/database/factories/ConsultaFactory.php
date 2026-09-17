<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'persona_id' => Persona::factory(),
            'entidad_nombre' => $this->faker->randomElement([
                'Banco Pichincha', 'Banco Guayaquil', 'Cooperativa JEP', 'Produbanco', 'Banco del Austro',
            ]),
            'motivo' => $this->faker->randomElement([
                'Solicitud de crédito de consumo', 'Renovación de tarjeta de crédito', 'Apertura de cuenta corriente',
            ]),
            'consultada_en' => now()->subDays($this->faker->numberBetween(1, 20)),
            'reconocida' => null,
        ];
    }

    public function noReconocida(): static
    {
        return $this->state(fn () => ['reconocida' => false]);
    }
}
