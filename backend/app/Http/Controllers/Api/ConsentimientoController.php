<?php

namespace App\Http\Controllers\Api;

use App\Domain\Casos\Exceptions\CasoNoEsperaAutorizacionException;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FirmarConsentimientoRequest;
use App\Http\Requests\Api\RevocarConsentimientoRequest;
use App\Http\Resources\ConsentimientoResource;
use App\Models\Caso;
use App\Models\Consentimiento;
use App\Services\Consentimientos\ConsentimientoService;

class ConsentimientoController extends Controller
{
    public function __construct(private readonly ConsentimientoService $consentimientos) {}

    /**
     * La persona firma: única puerta hacia la acción. Sin este paso ningún
     * agente puede avanzar el caso.
     */
    public function firmar(FirmarConsentimientoRequest $request, Caso $caso)
    {
        try {
            $consentimiento = $this->consentimientos->firmar($caso, $request->validated('contexto'));
        } catch (CasoNoEsperaAutorizacionException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (TransicionInvalidaException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new ConsentimientoResource($consentimiento))->response()->setStatusCode(201);
    }

    public function revocar(RevocarConsentimientoRequest $request, Consentimiento $consentimiento)
    {
        return new ConsentimientoResource($this->consentimientos->revocar($consentimiento));
    }
}
