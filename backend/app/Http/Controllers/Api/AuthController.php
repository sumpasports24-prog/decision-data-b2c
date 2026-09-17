<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\PersonaResource;
use App\Services\Identidad\AutenticacionService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AutenticacionService $autenticacion) {}

    public function login(LoginRequest $request)
    {
        $persona = $this->autenticacion->buscarPorCedula($request->validated('cedula'));

        if (! $persona) {
            return response()->json([
                'message' => 'No encontramos una identidad con esa cédula en el entorno de demo.',
            ], 404);
        }

        return response()->json([
            'token' => $this->autenticacion->emitirToken($persona),
            'persona' => new PersonaResource($persona),
        ]);
    }

    public function logout(Request $request)
    {
        $this->autenticacion->cerrarSesion($request->user());

        return response()->json(['message' => 'Sesión cerrada.']);
    }
}
