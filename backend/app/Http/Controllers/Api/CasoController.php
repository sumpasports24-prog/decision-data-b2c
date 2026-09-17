<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerCasoRequest;
use App\Http\Resources\CasoResource;
use App\Http\Resources\CasoResumenResource;
use App\Models\Caso;
use Illuminate\Http\Request;

class CasoController extends Controller
{
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
}
