<?php

namespace App\Domain\Casos;

enum CasoEstado: string
{
    case Detectado = 'detectado';
    case Notificado = 'notificado';
    case Autorizado = 'autorizado';
    case EnGestion = 'en_gestion';
    case Escalado = 'escalado';
    case Resuelto = 'resuelto';
    case Descartado = 'descartado';

    /**
     * Grafo de transiciones válidas. Cualquier salto fuera de este mapa es rechazado
     * por CaseStateMachine, sin excepción.
     *
     * `Descartado` es la rama corta: la persona confirma "sí fui yo" y el caso se
     * cierra ahí mismo, sin pasar por autorización ni gestión. No es un fallo del
     * flujo, es el camino que va a tomar la mayoría de las consultas — la mayoría
     * de las consultas SÍ se reconocen.
     */
    public function siguientesValidos(): array
    {
        return match ($this) {
            self::Detectado => [self::Notificado],
            self::Notificado => [self::Autorizado, self::Descartado],
            self::Autorizado => [self::EnGestion],
            self::EnGestion => [self::Resuelto, self::Escalado],
            self::Escalado => [self::Resuelto],
            self::Resuelto, self::Descartado => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->siguientesValidos(), true);
    }
}
