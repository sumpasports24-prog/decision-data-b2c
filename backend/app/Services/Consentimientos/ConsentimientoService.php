<?php

namespace App\Services\Consentimientos;

use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Domain\Casos\Exceptions\CasoNoEsperaAutorizacionException;
use App\Jobs\GestionarCasoJob;
use App\Models\Caso;
use App\Models\Consentimiento;

/**
 * Orquesta la firma y revocación de consentimiento desde el lado de la
 * persona. Las reglas de negocio en sí (qué transición es válida, si hace
 * falta consentimiento) siguen viviendo en el motor de casos
 * (App\Domain\Casos); este servicio solo coordina el caso de uso completo
 * de la pantalla: crear el registro, avisar al motor, encolar la gestión.
 */
class ConsentimientoService
{
    public function __construct(private readonly CaseStateMachine $motor) {}

    public function firmar(Caso $caso): Consentimiento
    {
        if ($caso->estado !== CasoEstado::Notificado) {
            throw new CasoNoEsperaAutorizacionException;
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

        $this->motor->transicionar($caso, CasoEstado::Autorizado, actor: 'persona');

        GestionarCasoJob::dispatch($caso);

        return $consentimiento;
    }

    public function revocar(Consentimiento $consentimiento): Consentimiento
    {
        if ($consentimiento->revocado_en !== null) {
            return $consentimiento;
        }

        $consentimiento->update(['revocado_en' => now()]);

        $consentimiento->caso->registrarEvento('persona', 'consentimiento_revocado', [
            'consentimiento_id' => $consentimiento->id,
        ]);

        return $consentimiento;
    }
}
