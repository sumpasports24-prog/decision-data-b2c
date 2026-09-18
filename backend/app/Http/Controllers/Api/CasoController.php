<?php

namespace App\Http\Controllers\Api;

use App\Domain\Casos\Exceptions\CasoNoEsperaAutorizacionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReconocerCasoRequest;
use App\Http\Requests\Api\VerCasoRequest;
use App\Http\Resources\CasoResource;
use App\Http\Resources\CasoResumenResource;
use App\Models\Caso;
use App\Services\Casos\CasoService;
use Illuminate\Http\Request;

class CasoController extends Controller
{
    public function __construct(private readonly CasoService $casos) {}

    public function index(Request $request)
    {
        $casos = $request->user()->casos()->with('consulta.entidad')->orderByDesc('abierto_en')->get();

        return CasoResumenResource::collection($casos);
    }

    public function show(VerCasoRequest $request, Caso $caso)
    {
        $caso->load(['consulta.entidad', 'eventos', 'documentos', 'consentimientos']);

        return new CasoResource($caso);
    }

    /**
     * "Sí, fui yo": la persona reconoce la consulta. Camino corto, sin
     * consentimiento ni Gestor — no hay nada que gestionar.
     */
    public function reconocer(ReconocerCasoRequest $request, Caso $caso)
    {
        try {
            $caso = $this->casos->reconocer($caso);
        } catch (CasoNoEsperaAutorizacionException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $caso->load(['consulta.entidad', 'eventos']);

        return new CasoResource($caso);
    }
}
