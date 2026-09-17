<?php

namespace Database\Factories;

use App\Models\Persona;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonaFactory extends Factory
{
    protected $model = Persona::class;

    public function definition(): array
    {
        return [
            'cedula_hash' => Persona::hashCedula((string) $this->faker->unique()->numerify('##########')),
            'nombre' => $this->faker->name(),
            'telefono_e164' => '+593'.$this->faker->numerify('#########'),
            'identidad_verificada_en' => now(),
        ];
    }
}
