<?php

namespace App\Policies;

use App\Models\Caso;
use App\Models\Persona;

class CasoPolicy
{
    public function view(Persona $persona, Caso $caso): bool
    {
        return $caso->persona_id === $persona->id;
    }

    public function autorizar(Persona $persona, Caso $caso): bool
    {
        return $caso->persona_id === $persona->id;
    }
}
