<?php

namespace App\Domain\Casos\Exceptions;

use RuntimeException;

class BitacoraInmutableException extends RuntimeException
{
    public function __construct(string $accion)
    {
        parent::__construct("La bitácora de eventos es append-only: no se permite {$accion}.");
    }
}
