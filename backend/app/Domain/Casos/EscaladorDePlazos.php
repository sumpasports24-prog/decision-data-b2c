<?php

namespace App\Domain\Casos;

use App\Models\Caso;
use Illuminate\Support\Collection;

/**
 * Corre periódicamente (ver routes/console.php) y escala automáticamente todo
 * caso en_gestion cuyo plazo legal ya venció, sin intervención humana.
 */
class EscaladorDePlazos
{
    public function __construct(
        private readonly CaseStateMachine $motor,
        private readonly DeadlineClock $reloj,
    ) {}

    /**
     * @return Collection<int, Caso> los casos que se acaban de escalar
     */
    public function ejecutar(): Collection
    {
        return Caso::query()
            ->where('estado', CasoEstado::EnGestion)
            ->whereNotNull('vence_en')
            ->get()
            ->filter(fn (Caso $caso) => $this->reloj->estaVencido($caso->vence_en))
            ->each(function (Caso $caso) {
                $this->motor->transicionar($caso, CasoEstado::Escalado, actor: 'sistema', contexto: [
                    'motivo' => 'plazo_vencido',
                    'vencio_en' => $caso->vence_en->toIso8601String(),
                ]);
            });
    }
}
