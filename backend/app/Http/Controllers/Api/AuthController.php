<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Persona;
use App\Rules\CedulaEcuatoriana;
use Illuminate\Http\Request;

/**
 * Verificación de identidad simulada sobre datos sintéticos: no hay
 * contraseña ni OTP real. Si la cédula tiene el formato válido y coincide
 * con una persona sembrada, se emite un token de sesión para el frontend.
 */
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $datos = $request->validate([
            'cedula' => ['required', 'string', new CedulaEcuatoriana],
        ]);

        $persona = Persona::where('cedula_hash', Persona::hashCedula($datos['cedula']))->first();

        if (! $persona) {
            return response()->json([
                'message' => 'No encontramos una identidad con esa cédula en el entorno de demo.',
            ], 404);
        }

        return response()->json([
            'token' => $persona->createToken('frontend')->plainTextToken,
            'persona' => [
                'id' => $persona->id,
                'nombre' => $persona->nombre,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
