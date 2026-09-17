<?php

namespace App\Domain\Casos\Exceptions;

use App\Domain\Casos\CasoEstado;
use RuntimeException;

class TransicionInvalidaException extends RuntimeException
{
    public function __construct(CasoEstado $desde, CasoEstado $hacia)
    {
        parent::__construct("Transición inválida: no se puede pasar de '{$desde->value}' a '{$hacia->value}'.");
    }
}
