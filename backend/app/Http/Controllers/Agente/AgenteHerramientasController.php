<?php

namespace App\Http\Controllers\Agente;

use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agente\AvanzarEstadoRequest;
use App\Http\Requests\Agente\NotificarPersonaRequest;
use App\Http\Requests\Agente\RegistrarEventoRequest;
use App\Http\Requests\Agente\VerificarConsentimientoRequest;
use App\Http\Resources\CasoResource;
use App\Http\Resources\ConsentimientoResource;
use App\Http\Resources\DocumentoResource;
use App\Http\Resources\EventoResource;
use App\Models\Caso;
use App\Services\Agente\HerramientasAgenteService;
use Illuminate\Http\Request;

/**
 * Las 6 herramientas del agente (contrato, sección 12 del brief). Cada
 * acción es un endpoint de Laravel protegido por VerificarTokenAgente: el
 * agente nunca toca la base ni el resto de la API directamente. Toda la
 * lógica vive en HerramientasAgenteService; este controlador solo valida
 * la entrada y traduce las excepciones del dominio a códigos HTTP.
 */
class AgenteHerramientasController extends Controller
{
    public function __construct(private readonly HerramientasAgenteService $herramientas) {}

    /** obtener_caso: lee el caso y su historial. */
    public function obtenerCaso(Caso $caso)
    {
        return new CasoResource($this->herramientas->obtenerCaso($caso));
    }

    /** verificar_consentimiento: confirma alcance y vigencia antes de actuar. */
    public function verificarConsentimiento(VerificarConsentimientoRequest $request, Caso $caso)
    {
        $resultado = $this->herramientas->verificarConsentimiento($caso, $request->validated('alcance'));

        return response()->json([
            'alcance' => $resultado['alcance'],
            'vigente' => $resultado['vigente'],
            'consentimiento' => $resultado['consentimiento'] ? new ConsentimientoResource($resultado['consentimiento']) : null,
        ]);
    }

    /** redactar_oposicion: genera el documento y lo guarda. */
    public function redactarOposicion(Request $request, Caso $caso)
    {
        try {
            $documento = $this->herramientas->redactarOposicion($caso, $request->attributes->get('agente_actor'));
        } catch (ConsentimientoRequeridoException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new DocumentoResource($documento))->response()->setStatusCode(201);
    }

    /** registrar_evento: escribe en la bitácora inmutable. */
    public function registrarEvento(RegistrarEventoRequest $request, Caso $caso)
    {
        $evento = $this->herramientas->registrarEvento(
            $caso,
            $request->attributes->get('agente_actor'),
            $request->validated('tipo'),
            $request->validated('carga') ?? [],
        );

        return (new EventoResource($evento))->response()->setStatusCode(201);
    }

    /** avanzar_estado: solicita una transición; el motor la valida o la rechaza. */
    public function avanzarEstado(AvanzarEstadoRequest $request, Caso $caso)
    {
        try {
            $actualizado = $this->herramientas->avanzarEstado(
                $caso,
                $request->estado(),
                $request->attributes->get('agente_actor'),
                $request->validated('contexto') ?? [],
            );
        } catch (TransicionInvalidaException|ConsentimientoRequeridoException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['estado' => $actualizado->estado->value]);
    }

    /** notificar_persona: envía el mensaje por el canal registrado. */
    public function notificarPersona(NotificarPersonaRequest $request, Caso $caso)
    {
        $resultado = $this->herramientas->notificarPersona(
            $caso,
            $request->attributes->get('agente_actor'),
            $request->validated('plantilla'),
            $request->validated('variables') ?? [],
        );

        return response()->json($resultado);
    }
}
