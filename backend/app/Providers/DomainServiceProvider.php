<?php

namespace App\Providers;

use App\Services\IA\AgenteRedactorInterface;
use App\Services\IA\ClaudeRedactor;
use App\Services\IA\RedactorStub;
use App\Services\Notificaciones\LogDriver;
use App\Services\Notificaciones\NotificacionDriver;
use App\Services\Notificaciones\WhatsAppDriver;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Conmutable por NOTIFICATION_DRIVER=whatsapp|log. `log` es el default:
        // permite correr el recorrido completo sin credenciales de Meta.
        $this->app->bind(NotificacionDriver::class, function () {
            return match (config('casos.notification_driver')) {
                'whatsapp' => $this->app->make(WhatsAppDriver::class),
                default => $this->app->make(LogDriver::class),
            };
        });

        // Se activa solo cuando hay ANTHROPIC_API_KEY configurada; si no,
        // cae al stub declarado (ver RedactorStub y AI_USAGE.md).
        $this->app->bind(AgenteRedactorInterface::class, function () {
            return filled(config('services.anthropic.key'))
                ? $this->app->make(ClaudeRedactor::class)
                : $this->app->make(RedactorStub::class);
        });
    }

    public function boot(): void {}
}
