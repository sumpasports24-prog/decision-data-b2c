<?php

namespace App\Http\Controllers\Agente;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\ConsentimientoRequeridoException;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Http\Controllers\Controller;
use App\Models\Caso;
use App\Services\IA\AgenteRedactorInterface;
use App\Services\Notificaciones\NotificacionDriver;
use Illuminate\Http\Request;

/**
 * Las 6 herramientas del agente (contrato, sección 12 del brief). Cada
 * acción es un endpoint de Laravel protegido por VerificarTokenAgente: el
 * agente nunca toca la base ni el resto de la API directamente.
 */
class AgenteHerramientasController extends Controller
{
    public function __construct(
        private readonly CaseStateMachine $motor,
    ) {}

    /** obtener_caso: lee el caso y su historial. */
    public function obtenerCaso(Caso $caso)
    {
        $caso->load(['persona:id,nombre,telefono_e164', 'consulta', 'eventos', 'documentos', 'consentimientos']);

        return response()->json([
            'codigo' => $caso->codigo,
            'tipo' => $caso->tipo,
            'estado' => $caso->estado->value,
            'abierto_en' => $caso->abierto_en,
            'vence_en' => $caso->vence_en,
            'persona' => $caso->persona,
            'consulta' => $caso->consulta,
            'eventos' => $caso->eventos,
            'documentos' => $caso->documentos,
            'consentimientos' => $caso->consentimientos,
        ]);
    }

    /** verificar_consentimiento: confirma alcance y vigencia antes de actuar. */
    public function verificarConsentimiento(Request $request, Caso $caso)
    {
        $datos = $request->validate(['alcance' => 'nullable|string']);
        $alcance = $datos['alcance'] ?? $this->motor->alcancePorDefecto($caso);

        $consentimiento = $caso->consentimientoVigente($alcance);

        return response()->json([
            'alcance' => $alcance,
            'vigente' => $consentimiento !== null,
            'consentimiento' => $consentimiento,
        ]);
    }

    /** redactar_oposicion: genera el documento y lo guarda. */
    public function redactarOposicion(Request $request, Caso $caso, AgenteRedactorInterface $redactor)
    {
        $alcance = $this->motor->alcancePorDefecto($caso);

        if ($caso->consentimientoVigente($alcance) === null) {
            return response()->json([
                'message' => (new ConsentimientoRequeridoException)->getMessage(),
            ], 422);
        }

        $texto = $redactor->redactarOposicion($caso);

        $documento = $caso->documentos()->create([
            'tipo' => 'oposicion_lopdp',
            'contenido' => $texto,
            'generado_por' => $request->attributes->get('agente_actor'),
        ]);

        $caso->registrarEvento($request->attributes->get('agente_actor'), 'oposicion_generada', [
            'documento_id' => $documento->id,
        ]);

        return response()->json($documento, 201);
    }

    /** registrar_evento: escribe en la bitácora inmutable. */
    public function registrarEvento(Request $request, Caso $caso)
    {
        $datos = $request->validate([
            'tipo' => 'required|string|max:100',
            'carga' => 'nullable|array',
        ]);

        $evento = $caso->registrarEvento(
            $request->attributes->get('agente_actor'),
            $datos['tipo'],
            $datos['carga'] ?? [],
        );

        return response()->json($evento, 201);
    }

    /** avanzar_estado: solicita una transición; el motor la valida o la rechaza. */
    public function avanzarEstado(Request $request, Caso $caso)
    {
        $datos = $request->validate([
            'estado' => 'required|string|in:'.implode(',', array_map(fn ($e) => $e->value, CasoEstado::cases())),
            'contexto' => 'nullable|array',
        ]);

        try {
            $actualizado = $this->motor->transicionar(
                $caso,
                CasoEstado::from($datos['estado']),
                actor: $request->attributes->get('agente_actor'),
                contexto: $datos['contexto'] ?? [],
            );
        } catch (TransicionInvalidaException|ConsentimientoRequeridoException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['estado' => $actualizado->estado->value]);
    }

    /** notificar_persona: envía el mensaje por el canal registrado. */
    public function notificarPersona(Request $request, Caso $caso, NotificacionDriver $notificaciones)
    {
        $datos = $request->validate([
            'plantilla' => 'required|string|max:100',
            'variables' => 'nullable|array',
        ]);

        $resultado = $notificaciones->enviar($caso->persona, $datos['plantilla'], $datos['variables'] ?? []);

        $caso->registrarEvento($request->attributes->get('agente_actor'), 'notificacion_enviada', $resultado);

        return response()->json($resultado);
    }
}
