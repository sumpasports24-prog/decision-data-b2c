<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // El frontend espera la forma del recurso directo en la raíz del
        // JSON (`{ "codigo": ... }`), no envuelta en `{ "data": {...} }`.
        JsonResource::withoutWrapping();
    }
}
