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
        $casos = $persona->casos()->with('consulta.entidad')->orderByDesc('abierto_en')->get();

        return [
            'persona' => $persona,
            'score' => $this->scoreDeContexto($persona),
            'huella_de_consulta' => $persona->consultas()->with('entidad')->orderByDesc('consultada_en')->get(),
            'casos' => $casos,
            'alertas' => $casos->where('estado', CasoEstado::Notificado)->values(),
        ];
    }

    /**
     * Score sintético de contexto: no es el producto (ver sección 9 del
     * brief), solo da algo de textura a la pantalla. El total es
     * determinista por persona para que no "salte" entre refrescos; el
     * desglose en factores sí lee señales reales de la persona (consultas
     * no reconocidas, antigüedad verificada) para poder explicar el número
     * en vez de solo mostrarlo — es la respuesta a "¿por qué no aplico?".
     */
    private function scoreDeContexto(Persona $persona): array
    {
        $total = 700 + (($persona->id * 37) % 300);
        $consultasNoReconocidas = $persona->consultas()->where('reconocida', false)->count();
        $totalConsultas = $persona->consultas()->count();
        $antiguedadMeses = $persona->identidad_verificada_en
            ? (int) $persona->identidad_verificada_en->diffInMonths(now())
            : 0;

        return [
            'total' => $total,
            'factores' => [
                [
                    'clave' => 'pago',
                    'etiqueta' => 'Historial de pago',
                    'peso' => 0.35,
                    'direccion' => $total >= 800 ? 'positivo' : 'neutro',
                    'detalle' => 'Puntualidad reportada por las entidades que te han consultado.',
                ],
                [
                    'clave' => 'endeudamiento',
                    'etiqueta' => 'Consultas sin resolver',
                    'peso' => 0.30,
                    'direccion' => $consultasNoReconocidas > 0 ? 'negativo' : 'positivo',
                    'detalle' => $consultasNoReconocidas > 0
                        ? "Tienes {$consultasNoReconocidas} consulta(s) sin reconocer pesando en contra hasta que se resuelvan."
                        : 'No tienes consultas sin reconocer pesando en tu contra.',
                ],
                [
                    'clave' => 'antiguedad',
                    'etiqueta' => 'Antigüedad verificada',
                    'peso' => 0.15,
                    'direccion' => $antiguedadMeses >= 12 ? 'positivo' : 'neutro',
                    'detalle' => $antiguedadMeses > 0
                        ? "Identidad verificada hace {$antiguedadMeses} meses."
                        : 'Identidad verificada recientemente.',
                ],
                [
                    'clave' => 'huella',
                    'etiqueta' => 'Consultas recientes',
                    'peso' => 0.12,
                    'direccion' => $totalConsultas > 3 ? 'negativo' : 'neutro',
                    'detalle' => "{$totalConsultas} consulta(s) registradas sobre tu historial en total.",
                ],
                [
                    'clave' => 'mezcla',
                    'etiqueta' => 'Mezcla de entidades',
                    'peso' => 0.08,
                    'direccion' => 'neutro',
                    'detalle' => 'Diversidad de entidades que han solicitado tu información.',
                ],
            ],
        ];
    }
}
