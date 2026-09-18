<?php

namespace App\Domain\Casos\Agentes;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\DeadlineClock;
use App\Models\Caso;
use App\Models\Consulta;
use App\Services\Notificaciones\NotificacionDriver;
use Illuminate\Support\Collection;

/**
 * Corre sin usuario, como job programado (ver routes/console.php). Encuentra
 * consultas SIN REVISAR (reconocida = null) y sin caso todavía, abre el
 * caso, arranca el reloj del plazo legal y notifica a la persona para que
 * decida. No razona con IA: es determinista a propósito, para que la
 * detección sea auditable.
 *
 * A propósito NO filtra por `reconocida = false`: el sistema no puede saber
 * de antemano que una consulta "no se reconoce" — eso solo lo decide la
 * persona (ver UC-03). El Centinela vigila TODA consulta nueva sin revisar,
 * sea o no, al final, algo que la persona reconozca. `false` ya no es una
 * condición de entrada: es el resultado de que la persona dijo que no.
 */
class Centinela
{
    public function __construct(
        private readonly CaseStateMachine $motor,
        private readonly DeadlineClock $reloj,
        private readonly NotificacionDriver $notificaciones,
    ) {}

    /**
     * @return Collection<int, Caso>
     */
    public function detectar(): Collection
    {
        return Consulta::query()
            ->whereNull('reconocida')
            ->whereDoesntHave('caso')
            ->get()
            ->map(fn (Consulta $consulta) => $this->abrirCaso($consulta));
    }

    private function abrirCaso(Consulta $consulta): Caso
    {
        $abiertoEn = now();

        $caso = Caso::create([
            'persona_id' => $consulta->persona_id,
            'consulta_id' => $consulta->id,
            'tipo' => Caso::TIPO_CONSULTA_NO_RECONOCIDA,
            'estado' => CasoEstado::Detectado,
            'abierto_en' => $abiertoEn,
            'vence_en' => $this->reloj->calcularVencimiento($abiertoEn),
        ]);

        $caso->registrarEvento('centinela', 'caso_creado', [
            'consulta_id' => $consulta->id,
            'entidad' => $consulta->entidad_nombre,
        ]);

        $resultado = $this->notificaciones->enviar(
            $caso->persona,
            plantilla: 'consulta_no_reconocida',
            variables: [$caso->persona->nombre, $consulta->entidad_nombre, $caso->codigo],
        );

        $caso->registrarEvento('centinela', 'notificacion_enviada', $resultado);

        return $this->motor->transicionar($caso, CasoEstado::Notificado, actor: 'centinela');
    }
}
