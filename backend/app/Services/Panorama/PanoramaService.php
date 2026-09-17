<?php

namespace App\Services\Panorama;

use App\Domain\Casos\CasoEstado;
use App\Models\Persona;

/**
 * Arma el resumen de la pantalla Panorama: puntaje de contexto, huella de
 * consulta y casos. No es un modelo ni un motor de dominio, es una
 * orquestación de lectura pura para una pantalla concreta.
 */
class PanoramaService
{
    public function resumenPara(Persona $persona): array
    {
        $casos = $persona->casos()->with('consulta')->orderByDesc('abierto_en')->get();

        return [
            'persona' => $persona,
            'score' => $this->scoreDeContexto($persona),
            'huella_de_consulta' => $persona->consultas()->orderByDesc('consultada_en')->get(),
            'casos' => $casos,
            'alertas' => $casos->where('estado', CasoEstado::Notificado)->values(),
        ];
    }

    /**
     * Score sintético de contexto: no es el producto (ver sección 9 del
     * brief), solo da algo de textura a la pantalla. Determinista por
     * persona para que no "salte" entre refrescos.
     */
    private function scoreDeContexto(Persona $persona): int
    {
        return 700 + (($persona->id * 37) % 300);
    }
}
