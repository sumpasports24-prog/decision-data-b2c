<?php

namespace App\Console\Commands;

use App\Domain\Casos\Agentes\Centinela;
use Illuminate\Console\Command;

class EjecutarCentinela extends Command
{
    protected $signature = 'centinela:ejecutar';

    protected $description = 'Detecta consultas no reconocidas sin caso y abre el caso correspondiente.';

    public function handle(Centinela $centinela): int
    {
        $casos = $centinela->detectar();

        $this->info("Centinela: {$casos->count()} caso(s) abierto(s).");

        foreach ($casos as $caso) {
            $this->line("  - {$caso->codigo} · {$caso->persona->nombre} · {$caso->consulta->entidad_nombre}");
        }

        return self::SUCCESS;
    }
}
