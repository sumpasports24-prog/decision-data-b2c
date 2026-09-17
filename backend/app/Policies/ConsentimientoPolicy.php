<?php

namespace App\Policies;

use App\Models\Consentimiento;
use App\Models\Persona;

class ConsentimientoPolicy
{
    public function revocar(Persona $persona, Consentimiento $consentimiento): bool
    {
        return $consentimiento->caso->persona_id === $persona->id;
    }
}
