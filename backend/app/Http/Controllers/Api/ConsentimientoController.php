<?php

namespace App\Http\Controllers\Api;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\TransicionInvalidaException;
use App\Http\Controllers\Controller;
use App\Jobs\GestionarCasoJob;
use App\Models\Caso;
use App\Models\Consentimiento;
use Illuminate\Http\Request;

class ConsentimientoController extends Controller
{
    public function __construct(private readonly CaseStateMachine $motor) {}

    /**
     * La persona firma: única puerta hacia la acción. Sin este paso ningún
     * agente puede avanzar el caso.
     */
    public function firmar(Request $request, Caso $caso)
    {
        if ($caso->persona_id !== $request->user()->id) {
            abort(403, 'No tienes permiso sobre este caso.');
        }

        if ($caso->estado !== CasoEstado::Notificado) {
            return response()->json([
                'message' => 'Este caso no está esperando autorización en este momento.',
            ], 409);
        }

        $alcance = $this->motor->alcancePorDefecto($caso);
        $textoConfig = config('casos.texto_consentimiento');

        $consentimiento = Consentimiento::create([
            'caso_id' => $caso->id,
            'alcance' => $alcance,
            'texto_version' => $textoConfig['version'].': '.$textoConfig['texto'],
            'firmado_en' => now(),
            'canal' => 'web',
        ]);

        $caso->registrarEvento('persona', 'consentimiento_firmado', ['consentimiento_id' => $consentimiento->id]);

        try {
            $this->motor->transicionar($caso, CasoEstado::Autorizado, actor: 'persona');
        } catch (TransicionInvalidaException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        GestionarCasoJob::dispatch($caso);

        return response()->json($consentimiento, 201);
    }

    public function revocar(Request $request, Consentimiento $consentimiento)
    {
        $consentimiento->loadMissing('caso');

        if ($consentimiento->caso->persona_id !== $request->user()->id) {
            abort(403, 'No tienes permiso sobre este consentimiento.');
        }

        if ($consentimiento->revocado_en !== null) {
            return response()->json($consentimiento);
        }

        $consentimiento->update(['revocado_en' => now()]);

        $consentimiento->caso->registrarEvento('persona', 'consentimiento_revocado', [
            'consentimiento_id' => $consentimiento->id,
        ]);

        return response()->json($consentimiento);
    }
}
