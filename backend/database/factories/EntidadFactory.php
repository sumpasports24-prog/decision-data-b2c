<?php

namespace Database\Factories;

use App\Models\Entidad;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntidadFactory extends Factory
{
    protected $model = Entidad::class;

    public function definition(): array
    {
        return [
            'nombre' => $this->faker->unique()->randomElement([
                'Banco Pichincha', 'Banco Guayaquil', 'Cooperativa JEP', 'Produbanco',
                'Banco del Austro', 'Banco Bolivariano', 'Cooperativa Andalucía',
            ]),
            'tipo' => 'entidad_financiera',
            'email_contacto' => null,
        ];
    }
}
