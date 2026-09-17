<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caso;
use Illuminate\Http\Request;

class CasoController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->casos()->with('consulta')->orderByDesc('abierto_en')->get()
        );
    }

    public function show(Request $request, Caso $caso)
    {
        $this->autorizarLectura($request, $caso);

        $caso->load(['consulta', 'eventos', 'documentos', 'consentimientos']);

        return response()->json($caso);
    }

    private function autorizarLectura(Request $request, Caso $caso): void
    {
        if ($caso->persona_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para leer este caso.');
        }
    }
}
