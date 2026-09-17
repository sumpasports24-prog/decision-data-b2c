<?php

namespace Tests\Unit\Models;

use App\Domain\Casos\Exceptions\BitacoraInmutableException;
use App\Models\Caso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventoInmutableTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_evento_no_se_puede_modificar_una_vez_creado(): void
    {
        $caso = Caso::factory()->create();
        $evento = $caso->registrarEvento('sistema', 'caso_creado');

        $this->expectException(BitacoraInmutableException::class);

        $evento->tipo = 'intento_de_alteracion';
        $evento->save();
    }

    public function test_un_evento_no_se_puede_eliminar(): void
    {
        $caso = Caso::factory()->create();
        $evento = $caso->registrarEvento('sistema', 'caso_creado');

        $this->expectException(BitacoraInmutableException::class);

        $evento->delete();
    }
}
