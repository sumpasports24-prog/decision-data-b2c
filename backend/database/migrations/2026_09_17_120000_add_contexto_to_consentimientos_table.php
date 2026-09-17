<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La persona puede agregar, al momento de firmar, lo que recuerda sobre la
 * consulta que no reconoce. Ese texto entra al prompt del Gestor (ver
 * ClaudeRedactor) para que la oposición cite un hecho concreto de la
 * persona en vez de solo repetir los datos que ya trae el caso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consentimientos', function (Blueprint $table) {
            $table->text('contexto')->nullable()->after('texto_version');
        });
    }

    public function down(): void
    {
        Schema::table('consentimientos', function (Blueprint $table) {
            $table->dropColumn('contexto');
        });
    }
};
