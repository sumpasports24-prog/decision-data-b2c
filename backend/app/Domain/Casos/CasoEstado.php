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

    /**
     * Grafo de transiciones válidas. Cualquier salto fuera de este mapa es rechazado
     * por CaseStateMachine, sin excepción.
     */
    public function siguientesValidos(): array
    {
        return match ($this) {
            self::Detectado => [self::Notificado],
            self::Notificado => [self::Autorizado],
            self::Autorizado => [self::EnGestion],
            self::EnGestion => [self::Resuelto, self::Escalado],
            self::Escalado => [self::Resuelto],
            self::Resuelto => [],
        };
    }

    public function puedeTransicionarA(self $destino): bool
    {
        return in_array($destino, $this->siguientesValidos(), true);
    }
}
