<?php

namespace App\Domain\Casos\Exceptions;

use RuntimeException;

class ConsentimientoRequeridoException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'No existe un consentimiento firmado y vigente para este caso y esta entidad. '
            .'Ningún agente puede avanzar el caso a en_gestion sin él.'
        );
    }
}
