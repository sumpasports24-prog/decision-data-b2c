<?php

namespace App\Http\Controllers\Api;

use App\Domain\Casos\CasoEstado;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PanoramaController extends Controller
{
    public function index(Request $request)
    {
        $persona = $request->user();

        $casos = $persona->casos()->with('consulta')->orderByDesc('abierto_en')->get();
        $consultas = $persona->consultas()->orderByDesc('consultada_en')->get();

        return response()->json([
            'persona' => ['id' => $persona->id, 'nombre' => $persona->nombre],
            // Score sintético de contexto, no es el producto: ver sección 9 del brief.
            'score' => 700 + (($persona->id * 37) % 300),
            'huella_de_consulta' => $consultas,
            'casos' => $casos,
            'alertas' => $casos->whereIn('estado', [CasoEstado::Notificado])->values(),
        ]);
    }
}
