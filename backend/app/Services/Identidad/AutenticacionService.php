<?php

namespace App\Services\Identidad;

use App\Models\Persona;

/**
 * Verificación de identidad simulada sobre datos sintéticos (ver
 * limitaciones en el README): no hay contraseña ni OTP real. Encapsula el
 * único mecanismo de sesión de la plataforma: un token Sanctum por cédula.
 */
class AutenticacionService
{
    public function buscarPorCedula(string $cedula): ?Persona
    {
        return Persona::where('cedula_hash', Persona::hashCedula($cedula))->first();
    }

    public function emitirToken(Persona $persona): string
    {
        return $persona->createToken('frontend')->plainTextToken;
    }

    public function cerrarSesion(Persona $persona): void
    {
        $persona->currentAccessToken()->delete();
    }
}
