<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada fila creada por la simulación (datos de prueba) para poder borrarlas
 * después sin tocar los datos reales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulacion_registros', function (Blueprint $table) {
            $table->id();
            $table->string('tabla', 80);
            $table->string('llave', 40);
            $table->string('valor', 40);
            $table->timestamp('created_at')->nullable();
            $table->index(['tabla', 'valor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulacion_registros');
    }
};
