<?php

namespace App\Jobs;

use App\Domain\Casos\Agentes\Gestor;
use App\Models\Caso;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * El worker (ver docker-compose.yml) procesa esto en background: la persona
 * ve `autorizado` de inmediato al firmar y, segundos después, `en_gestion`
 * cuando el Gestor termina de redactar. Así el consentimiento nunca bloquea
 * la respuesta HTTP.
 */
class GestionarCasoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly Caso $caso) {}

    public function handle(Gestor $gestor): void
    {
        $gestor->gestionar($this->caso->fresh());
    }
}
