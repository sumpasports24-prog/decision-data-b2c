<?php

namespace App\Http\Middleware;

use App\Models\AgenteToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege las 6 herramientas del agente (routes/api.php, prefijo
 * /api/agente). Exige un bearer token vigente, emitido para el caso exacto
 * de la ruta, con la habilidad que esa ruta requiere.
 */
class VerificarTokenAgente
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $plano = $request->bearerToken();

        if (! $plano) {
            return response()->json(['message' => 'Falta el token del agente.'], 401);
        }

        $token = AgenteToken::resolver($plano);

        if (! $token || ! $token->estaVigente()) {
            return response()->json(['message' => 'Token de agente inválido o expirado.'], 401);
        }

        $caso = $request->route('caso');

        if ((int) $token->caso_id !== (int) $caso->id) {
            return response()->json(['message' => 'El token no tiene alcance sobre este caso.'], 403);
        }

        if (! $token->puede($ability)) {
            return response()->json(['message' => "El token no tiene la habilidad '{$ability}'."], 403);
        }

        $token->forceFill(['usado_en' => now()])->save();
        $request->attributes->set('agente_actor', $token->actor);

        return $next($request);
    }
}
