<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Datos ampliados del animal, costos completos de adquisición,
 * historial de corrales, fotos, bajas por tipo y pesajes con CC.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- Costos de adquisición por guía de compra ---
        Schema::table('facturaGanado', function (Blueprint $table) {
            $table->decimal('costo_flete', 12, 2)->default(0)->after('estado');
            $table->decimal('costo_comision', 12, 2)->default(0)->after('costo_flete');
            $table->decimal('costo_tramites', 12, 2)->default(0)->after('costo_comision'); // guía REEMO, certificado, pruebas
            $table->decimal('otros_costos', 12, 2)->default(0)->after('costo_tramites');
            $table->decimal('peso_origen_kg', 12, 2)->nullable()->after('otros_costos');   // báscula del vendedor
            $table->string('guia_reemo', 60)->nullable()->after('peso_origen_kg');
            $table->string('upp_origen', 40)->nullable()->after('guia_reemo');
        });

        // --- Datos ampliados del animal ---
        Schema::table('ganado', function (Blueprint $table) {
            $table->string('arete_siniiga', 30)->nullable()->after('areteID')->index();
            $table->string('fierro', 80)->nullable()->after('arete_siniiga');
            $table->string('color_senas', 255)->nullable()->after('fierro');
            $table->string('procedencia', 150)->nullable()->after('color_senas');      // rancho / municipio de origen
            $table->string('upp_origen', 40)->nullable()->after('procedencia');
            $table->string('denticion', 30)->nullable()->after('upp_origen');          // Dientes de leche, 2, 4, 6 dientes, Boca llena
            $table->decimal('peso_origen', 8, 2)->nullable()->after('pesoActual');     // peso en báscula del vendedor
            $table->date('fecha_ingreso_corral')->nullable()->after('fechaCompra');
            $table->decimal('costo_adicional_compra', 12, 2)->default(0)->after('precioCompra'); // prorrateo flete/comisión/trámites
        });

        Schema::create('movimientos_corral', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ganado_id');
            $table->foreign('ganado_id')->references('GanadoID')->on('ganado')->cascadeOnDelete();
            $table->foreignId('corral_origen_id')->nullable()->constrained('corrales_lotes')->nullOnDelete();
            $table->foreignId('corral_destino_id')->nullable()->constrained('corrales_lotes')->nullOnDelete();
            $table->date('fecha')->index();
            $table->string('motivo', 150)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('ganado_fotos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ganado_id');
            $table->foreign('ganado_id')->references('GanadoID')->on('ganado')->cascadeOnDelete();
            $table->string('ruta');
            $table->string('descripcion', 150)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // --- Bajas por tipo (no solo muerte) ---
        Schema::table('bajas_ganado', function (Blueprint $table) {
            $table->string('tipo_baja', 20)->default('Muerte')->after('ganado_id')->index(); // Muerte, Sacrificio, Robo, Extravio, Descarte
            $table->decimal('valor_perdido_mxn', 14, 2)->nullable()->after('ultimo_peso');
            $table->decimal('valor_recuperado_mxn', 14, 2)->default(0)->after('valor_perdido_mxn'); // venta de canal de desecho, seguro...
        });

        // Estatus "Baja" para robo / extravío / descarte
        Schema::table('ganado', function (Blueprint $table) {
            $table->enum('status', ['Activo', 'Vendido', 'Muerto', 'Exportado', 'Baja'])->default('Activo')->change();
        });

        // --- Pesajes con condición corporal y ayuno ---
        Schema::table('pesajes_ganado', function (Blueprint $table) {
            $table->decimal('condicion_corporal', 3, 1)->nullable()->after('gdp');
            $table->boolean('en_ayuno')->default(false)->after('condicion_corporal');
        });
    }

    public function down(): void
    {
        Schema::table('pesajes_ganado', fn (Blueprint $t) => $t->dropColumn(['condicion_corporal', 'en_ayuno']));
        Schema::table('bajas_ganado', fn (Blueprint $t) => $t->dropColumn(['tipo_baja', 'valor_perdido_mxn', 'valor_recuperado_mxn']));
        Schema::dropIfExists('ganado_fotos');
        Schema::dropIfExists('movimientos_corral');
        Schema::table('ganado', fn (Blueprint $t) => $t->dropColumn([
            'arete_siniiga', 'fierro', 'color_senas', 'procedencia', 'upp_origen', 'denticion',
            'peso_origen', 'fecha_ingreso_corral', 'costo_adicional_compra',
        ]));
        Schema::table('facturaGanado', fn (Blueprint $t) => $t->dropColumn([
            'costo_flete', 'costo_comision', 'costo_tramites', 'otros_costos', 'peso_origen_kg', 'guia_reemo', 'upp_origen',
        ]));
    }
};
