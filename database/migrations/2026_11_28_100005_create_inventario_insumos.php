<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Inventario de alimento, medicamentos y otros insumos con kardex.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 40)->unique();
            $table->string('nombre', 150);
            $table->string('tipo', 20)->index();                    // alimento | medicamento | otro
            $table->string('unidad', 15)->default('kg');            // kg, l, ml, dosis, pieza, saco
            $table->decimal('existencia', 14, 3)->default(0);
            $table->decimal('costo_promedio', 14, 4)->default(0);   // costo promedio ponderado por unidad
            $table->decimal('stock_minimo', 14, 3)->default(0);
            $table->unsignedSmallInteger('dias_retiro')->nullable(); // medicamentos
            $table->string('presentacion', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('movimientos_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->string('tipo', 10);                              // entrada | salida | ajuste
            $table->date('fecha')->index();
            $table->decimal('cantidad', 14, 3);                      // positiva; el tipo define el signo (ajuste puede ser +/-)
            $table->decimal('costo_unitario', 14, 4)->default(0);
            $table->decimal('existencia_resultante', 14, 3)->default(0);
            $table->string('lote', 60)->nullable();
            $table->date('fecha_caducidad')->nullable();
            $table->string('proveedor', 150)->nullable();
            $table->string('referencia', 150)->nullable();           // factura, consumo #, tratamiento #
            $table->string('origen_type', 80)->nullable();           // modelo que generó el movimiento
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->foreignId('corral_lote_id')->nullable()->constrained('corrales_lotes')->nullOnDelete();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['origen_type', 'origen_id']);
        });

        Schema::create('racion_ingredientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('racion_id')->constrained('raciones_alimento')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumos')->cascadeOnDelete();
            $table->decimal('porcentaje', 6, 3);                     // % en base tal como se ofrece
            $table->timestamps();
            $table->unique(['racion_id', 'insumo_id']);
        });

        Schema::table('tratamientos_sanitarios', function (Blueprint $table) {
            $table->foreignId('insumo_id')->nullable()->after('ganado_id')->constrained('insumos')->nullOnDelete();
            $table->decimal('cantidad_insumo', 12, 3)->nullable()->after('insumo_id');
            $table->string('lote_medicamento', 60)->nullable()->after('cantidad_insumo');
            $table->date('caducidad_medicamento')->nullable()->after('lote_medicamento');
        });
    }

    public function down(): void
    {
        Schema::table('tratamientos_sanitarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insumo_id');
            $table->dropColumn(['cantidad_insumo', 'lote_medicamento', 'caducidad_medicamento']);
        });
        Schema::dropIfExists('racion_ingredientes');
        Schema::dropIfExists('movimientos_insumo');
        Schema::dropIfExists('insumos');
    }
};
