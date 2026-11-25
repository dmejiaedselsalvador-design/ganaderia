<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Receta de la dieta capturada por cantidades (ej. 2 kg de maíz, 2 l de agua, 2 lb de sal):
 * se guarda lo capturado y el sistema calcula el % de cada ingrediente.
 * Insumos que no se controlan en bodega (agua de pozo, etc.): no se descuentan ni generan faltantes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('racion_ingredientes', function (Blueprint $table) {
            $table->decimal('cantidad', 12, 3)->nullable()->after('porcentaje');
            $table->string('unidad_captura', 10)->nullable()->after('cantidad');
        });

        Schema::table('insumos', function (Blueprint $table) {
            $table->boolean('controla_existencia')->default(true)->after('factor_kg');
        });
    }

    public function down(): void
    {
        Schema::table('racion_ingredientes', fn (Blueprint $t) => $t->dropColumn(['cantidad', 'unidad_captura']));
        Schema::table('insumos', fn (Blueprint $t) => $t->dropColumn('controla_existencia'));
    }
};
