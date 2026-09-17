<?php

namespace Database\Seeders;

use App\Domain\Casos\Agentes\Centinela;
use App\Domain\Casos\Agentes\Gestor;
use App\Domain\Casos\CaseStateMachine;
use App\Domain\Casos\CasoEstado;
use App\Models\Consentimiento;
use App\Models\Consulta;
use App\Models\Entidad;
use App\Models\Persona;
use Illuminate\Database\Seeder;

/**
 * Escenario sintético para la demo y para desarrollo local. Reutiliza los
 * mismos servicios de dominio que usa la app en producción (Centinela,
 * Gestor, CaseStateMachine) en vez de insertar estados "a mano", para que
 * los datos sembrados respeten exactamente las mismas reglas de negocio.
 *
 * Cédula de demo para el login (ver AuthController): 1710034065
 */
class EscenarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        // Idempotente: en Docker el contenedor `app` corre este seeder en cada
        // arranque. Si ya hay datos, no duplica nada.
        if (Persona::query()->exists()) {
            return;
        }

        $motor = app(CaseStateMachine::class);

        $bancoPichincha = Entidad::create(['nombre' => 'Banco Pichincha']);
        $jep = Entidad::create(['nombre' => 'Cooperativa JEP']);
        $produbanco = Entidad::create(['nombre' => 'Produbanco']);
        $bancoGuayaquil = Entidad::create(['nombre' => 'Banco Guayaquil']);
        $bancoAustro = Entidad::create(['nombre' => 'Banco del Austro']);

        // --- Persona principal de la demo -------------------------------
        $ana = Persona::create([
            'cedula_hash' => Persona::hashCedula('1710034065'),
            'nombre' => 'Ana María Torres',
            'telefono_e164' => '+593987654321',
            'identidad_verificada_en' => now()->subDays(40),
        ]);

        // Consulta reconocida: aparece en la huella, no genera caso.
        Consulta::create([
            'persona_id' => $ana->id,
            'entidad_id' => $bancoPichincha->id,
            'motivo' => 'Solicitud de crédito de consumo',
            'consultada_en' => now()->subDays(10),
            'reconocida' => true,
        ]);

        // Consulta no reconocida que ya avanzó hasta "notificado": para firmar en vivo desde el frontend.
        // El motivo menciona Cuenca a propósito: es el mismo dato que el guion de demo usa como
        // contexto al firmar ("nunca estuve en Cuenca"), para que el porqué de la disputa se lea
        // solo, sin tener que explicarlo en voz alta.
        $consultaJep = Consulta::create([
            'persona_id' => $ana->id,
            'entidad_id' => $jep->id,
            'motivo' => 'Solicitud de crédito de consumo — agencia Cuenca',
            'consultada_en' => now()->subDays(3),
            'reconocida' => false,
        ]);

        // Consulta que ya se gestionó por completo hasta "en_gestion", con plazo vencido: para escalar en vivo.
        $consultaProdu = Consulta::create([
            'persona_id' => $ana->id,
            'entidad_id' => $produbanco->id,
            'motivo' => 'Apertura de cuenta corriente — agencia Portoviejo',
            'consultada_en' => now()->subDays(25),
            'reconocida' => false,
        ]);

        // Consulta que llega hasta "resuelto": para mostrar el ciclo completo cerrado.
        $consultaGuayaquil = Consulta::create([
            'persona_id' => $ana->id,
            'entidad_id' => $bancoGuayaquil->id,
            'motivo' => 'Tarjeta de crédito adicional a nombre de un tercero',
            'consultada_en' => now()->subDays(40),
            'reconocida' => false,
        ]);

        // El Centinela abre y notifica los 3 casos (JEP, Produbanco, Guayaquil) de una vez, como en producción.
        app(Centinela::class)->detectar();

        // Esta consulta se crea DESPUÉS de correr el Centinela a propósito: queda sin
        // caso, lista para que la detección se demuestre en vivo durante la presentación
        // corriendo `php artisan centinela:ejecutar`.
        Consulta::create([
            'persona_id' => $ana->id,
            'entidad_id' => $bancoAustro->id,
            'motivo' => 'Consulta de score crediticio por canal digital no registrado a su nombre',
            'consultada_en' => now()->subHours(6),
            'reconocida' => false,
        ]);

        // JEP se queda en "notificado": listo para firmar consentimiento desde el frontend.
        // (No se toca más.)

        // Produbanco: la persona autoriza y el Gestor gestiona; luego forzamos el vencimiento
        // del plazo para poder escalar en vivo durante la presentación.
        $casoProdu = $consultaProdu->fresh()->caso;
        $this->autorizarYGestionar($motor, $casoProdu);
        $casoProdu->update(['vence_en' => now()->subDay()]);

        // Banco Guayaquil: recorrido completo hasta resuelto.
        $casoResuelto = $consultaGuayaquil->fresh()->caso;
        $this->autorizarYGestionar($motor, $casoResuelto);
        $motor->transicionar($casoResuelto->fresh(), CasoEstado::Resuelto, actor: 'sistema', contexto: [
            'motivo' => 'entidad_respondio_conforme',
        ]);

        // --- Personas secundarias, solo para que el dataset no luzca vacío ---
        Persona::create([
            'cedula_hash' => Persona::hashCedula('0901234567'),
            'nombre' => 'Carlos Salazar',
            'telefono_e164' => '+593991112233',
            'identidad_verificada_en' => now()->subDays(15),
        ])->consultas()->create([
            'entidad_id' => $bancoGuayaquil->id,
            'motivo' => 'Renovación de tarjeta de débito',
            'consultada_en' => now()->subDays(5),
            'reconocida' => true,
        ]);

        Persona::create([
            'cedula_hash' => Persona::hashCedula('1711223337'),
            'nombre' => 'María Belén Rosero',
            'telefono_e164' => '+593998887766',
            'identidad_verificada_en' => now()->subDays(2),
        ])->consultas()->create([
            'entidad_id' => $jep->id,
            'motivo' => 'Solicitud de microcrédito',
            'consultada_en' => now()->subDays(1),
            'reconocida' => true,
        ]);
    }

    private function autorizarYGestionar(CaseStateMachine $motor, $caso): void
    {
        $alcance = $motor->alcancePorDefecto($caso);

        Consentimiento::create([
            'caso_id' => $caso->id,
            'alcance' => $alcance,
            'texto_version' => config('casos.texto_consentimiento.version').': '.config('casos.texto_consentimiento.texto'),
            'firmado_en' => now()->subDays(20),
            'canal' => 'whatsapp',
        ]);

        $motor->transicionar($caso, CasoEstado::Autorizado, actor: 'persona');

        app(Gestor::class)->gestionar($caso->fresh());
    }
}
