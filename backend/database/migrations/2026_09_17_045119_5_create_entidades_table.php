<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Normaliza lo que antes era `consultas.entidad_nombre` (texto libre
 * repetido en cada fila) en su propia tabla. Además de evitar la
 * duplicación, da un lugar natural para datos que un caso real necesita
 * de la entidad reportante (a quién dirigir la oposición, con qué correo)
 * que hoy no existen porque no hacen falta para la demo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entidades', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('tipo')->default('entidad_financiera');
            $table->string('email_contacto')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entidades');
    }
};
