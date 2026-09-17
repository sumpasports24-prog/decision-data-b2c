<?php

namespace App\Domain\Casos\Exceptions;

use RuntimeException;

class CasoNoEsperaAutorizacionException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Este caso no está esperando autorización en este momento.');
    }
}
