<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calendario sanitario (aplicaciones programadas por corral) y eventos de enfermedad (morbilidad).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programaciones_sanitarias', function (Blueprint $table) {
            $table->id();
            $table->string('titulo', 150);
            $table->string('tipo_evento', 30);
            $table->foreignId('corral_lote_id')->nullable()->constrained('corrales_lotes')->nullOnDelete();
            $table->foreignId('insumo_id')->nullable()->constrained('insumos')->nullOnDelete();
            $table->date('fecha_programada')->index();
            $table->string('dosis', 50)->nullable();
            $table->string('via_administracion', 50)->nullable();
            $table->string('estado', 15)->default('pendiente');      // pendiente | aplicada | cancelada
            $table->timestamp('aplicada_at')->nullable();
            $table->unsignedInteger('cabezas_aplicadas')->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('eventos_enfermedad', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ganado_id');
            $table->foreign('ganado_id')->references('GanadoID')->on('ganado')->cascadeOnDelete();
            $table->date('fecha_inicio')->index();
            $table->string('diagnostico', 150);
            $table->text('sintomas')->nullable();
            $table->string('severidad', 10)->default('leve');          // leve | moderada | grave
            $table->boolean('en_enfermeria')->default(false);
            $table->boolean('es_recaida')->default(false);
            $table->date('fecha_alta')->nullable();
            $table->string('resultado', 20)->default('en_tratamiento'); // en_tratamiento | recuperado | cronico | murio
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('tratamientos_sanitarios', function (Blueprint $table) {
            $table->foreignId('evento_enfermedad_id')->nullable()->after('ganado_id')->constrained('eventos_enfermedad')->nullOnDelete();
            $table->foreignId('programacion_id')->nullable()->after('evento_enfermedad_id')->constrained('programaciones_sanitarias')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tratamientos_sanitarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('evento_enfermedad_id');
            $table->dropConstrainedForeignId('programacion_id');
        });
        Schema::dropIfExists('eventos_enfermedad');
        Schema::dropIfExists('programaciones_sanitarias');
    }
};
