<?php

namespace Database\Factories;

use App\Models\Caso;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsentimientoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'caso_id' => Caso::factory(),
            'alcance' => 'oposicion:Banco Pichincha',
            'texto_version' => 'v1: Autorizo a Decision Data a presentar oposición LOPDP en mi nombre ante la entidad indicada.',
            'firmado_en' => now(),
            'revocado_en' => null,
            'canal' => 'whatsapp',
        ];
    }

    public function sinFirmar(): static
    {
        return $this->state(fn () => ['firmado_en' => null]);
    }

    public function revocado(): static
    {
        return $this->state(fn () => ['firmado_en' => now()->subDay(), 'revocado_en' => now()]);
    }
}
