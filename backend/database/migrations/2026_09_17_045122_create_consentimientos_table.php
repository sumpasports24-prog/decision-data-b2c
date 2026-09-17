<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consentimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caso_id')->constrained('casos')->cascadeOnDelete();
            $table->string('alcance');
            $table->text('texto_version');
            $table->timestamp('firmado_en')->nullable();
            $table->timestamp('revocado_en')->nullable();
            $table->string('canal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consentimientos');
    }
};
