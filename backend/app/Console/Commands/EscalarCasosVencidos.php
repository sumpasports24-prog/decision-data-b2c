<?php

namespace App\Console\Commands;

use App\Domain\Casos\EscaladorDePlazos;
use Illuminate\Console\Command;

class EscalarCasosVencidos extends Command
{
    protected $signature = 'casos:escalar-vencidos';

    protected $description = 'Escala a la Superintendencia todo caso en_gestion cuyo plazo legal ya venció.';

    public function handle(EscaladorDePlazos $escalador): int
    {
        $escalados = $escalador->ejecutar();

        $this->info("Escalador: {$escalados->count()} caso(s) escalado(s).");

        foreach ($escalados as $caso) {
            $this->line("  - {$caso->codigo} venció el {$caso->vence_en->toDateString()}");
        }

        return self::SUCCESS;
    }
}
