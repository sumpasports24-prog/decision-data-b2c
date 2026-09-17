<?php

return [
    // Texto exacto que la persona autoriza al firmar. Si cambia, se sube la
    // versión: los consentimientos ya firmados guardan la versión que aceptaron,
    // nunca una referencia mutable a este valor.
    'texto_consentimiento' => [
        'version' => 'v1',
        'texto' => 'Autorizo a Decision Data a presentar, en mi nombre, una oposición al '
            .'tratamiento de datos personales (LOPDP) ante la entidad reportante de este caso, '
            .'y a dar seguimiento al trámite hasta su resolución o escalamiento.',
    ],

    // Conmutable sin credenciales de Meta: 'log' (default) escribe el mensaje
    // en el log de la app; 'whatsapp' llama a la Meta Cloud API de verdad.
    'notification_driver' => env('NOTIFICATION_DRIVER', 'log'),

    // Plazo para que la entidad responda la oposición LOPDP, en días hábiles.
    // Asunción documentada: el enunciado no fija un número; se usa un valor
    // razonable y configurable. Ajustar aquí si Decision Data confirma el plazo legal exacto.
    'dias_habiles_respuesta' => (int) env('CASOS_DIAS_HABILES_RESPUESTA', 15),

    // Feriados nacionales de Ecuador usados para el cálculo de días hábiles.
    // Lista de ejemplo para el año de la demo; en producción vendría de un
    // servicio o tabla mantenida, no hardcodeada.
    'feriados' => [
        '2026-01-01', // Año Nuevo
        '2026-02-16', // Carnaval (lunes)
        '2026-02-17', // Carnaval (martes)
        '2026-04-03', // Viernes Santo
        '2026-05-01', // Día del Trabajo
        '2026-05-24', // Batalla de Pichincha
        '2026-08-10', // Primer Grito de Independencia
        '2026-10-09', // Independencia de Guayaquil
        '2026-11-02', // Día de los Difuntos
        '2026-11-03', // Independencia de Cuenca
        '2026-12-25', // Navidad
    ],
];
