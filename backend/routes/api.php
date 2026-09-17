<?php

use App\Http\Controllers\Agente\AgenteHerramientasController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CasoController;
use App\Http\Controllers\Api\ConsentimientoController;
use App\Http\Controllers\Api\PanoramaController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/panorama', [PanoramaController::class, 'index']);

    Route::get('/casos', [CasoController::class, 'index']);
    Route::get('/casos/{caso}', [CasoController::class, 'show']);
    Route::post('/casos/{caso}/consentimiento', [ConsentimientoController::class, 'firmar']);

    Route::post('/consentimientos/{consentimiento}/revocar', [ConsentimientoController::class, 'revocar']);
});

/*
| Herramientas del agente (sección 12 del brief): endpoints de Laravel con
| token de alcance limitado por caso, expirable. Ningún agente entra por
| aquí sin un AgenteToken vigente para el caso exacto de la ruta.
*/
Route::prefix('agente/casos/{caso}')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [AgenteHerramientasController::class, 'obtenerCaso'])
        ->middleware('agente.token:obtener_caso');

    Route::post('/verificar-consentimiento', [AgenteHerramientasController::class, 'verificarConsentimiento'])
        ->middleware('agente.token:verificar_consentimiento');

    Route::post('/redactar-oposicion', [AgenteHerramientasController::class, 'redactarOposicion'])
        ->middleware('agente.token:redactar_oposicion');

    Route::post('/eventos', [AgenteHerramientasController::class, 'registrarEvento'])
        ->middleware('agente.token:registrar_evento');

    Route::post('/avanzar-estado', [AgenteHerramientasController::class, 'avanzarEstado'])
        ->middleware('agente.token:avanzar_estado');

    Route::post('/notificar-persona', [AgenteHerramientasController::class, 'notificarPersona'])
        ->middleware('agente.token:notificar_persona');
});
