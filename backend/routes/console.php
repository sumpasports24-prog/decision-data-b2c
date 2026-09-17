<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// El Centinela "corre sin usuario": cada 5 minutos en producción es un valor
// razonable para una detección casi inmediata sin saturar la base.
Schedule::command('centinela:ejecutar')->everyFiveMinutes()->withoutOverlapping();

// El reloj de plazos se revisa cada hora: los plazos se cuentan en días
// hábiles, no hace falta granularidad de minutos.
Schedule::command('casos:escalar-vencidos')->hourly()->withoutOverlapping();
