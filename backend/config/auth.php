<?php

use App\Models\Persona;

return [

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'personas'),
    ],

    /*
    | No hay un guard ni un modelo "User" genérico: la única entidad
    | autenticable de esta plataforma B2C es Persona. El guard `sanctum`
    | valida el bearer token que el frontend usa contra la API.
    */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'personas',
        ],

        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'personas',
        ],
    ],

    'providers' => [
        'personas' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', Persona::class),
        ],
    ],

    'passwords' => [
        'personas' => [
            'provider' => 'personas',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
