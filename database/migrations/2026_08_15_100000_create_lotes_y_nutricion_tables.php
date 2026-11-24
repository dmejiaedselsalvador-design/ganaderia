<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Corrales y Lotes de Engorda (Feedlot Management)
        if (!Schema::hasTable('corrales_lotes')) {
            Schema::create('corrales_lotes', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 50)->unique(); // Ej. COR-01, LOTE-ENG-A
                $table->string('nombre', 100); // Ej. Corral 1 - Adaptación Norte
                $table->enum('fase_engorda', ['Recepcion', 'Crecimiento', 'Finalizacion'])->default('Recepcion');
                $table->integer('capacidad_maxima')->default(30);
                $table->decimal('peso_promedio_objetivo', 8, 2)->default(480.00); // kg meta para venta
                $table->date('fecha_inicio_lote')->nullable();
                $table->date('fecha_proyectada_salida')->nullable();
                $table->enum('estado', ['Activo', 'Vacio', 'En Cuarentena', 'Cerrado'])->default('Activo');
                $table->text('observaciones')->nullable();
                $table->timestamps();
            });
        }

        // 2. Catálogo de Raciones y Dietas Técnicas de Engorda
        if (!Schema::hasTable('raciones_alimento')) {
            Schema::create('raciones_alimento', function (Blueprint $table) {
                $table->id();
                $table->string('codigo', 50)->unique(); // RAC-01
                $table->string('nombre', 150);
                $table->enum('fase', ['Recepcion', 'Crecimiento', 'Finalizacion', 'Mantenimiento'])->default('Recepcion');
                $table->decimal('costo_por_kg', 10, 2); // Costo por kg en Pesos Mexicanos (MXN)
                $table->decimal('materia_seca_pct', 5, 2)->default(88.00); // % MS
                $table->decimal('proteina_cruda_pct', 5, 2)->default(14.00); // % PC
                $table->decimal('energia_metabolizable_mcal', 5, 2)->default(2.80); // Mcal/kg MS
                $table->text('descripcion_ingredientes')->nullable();
                $table->boolean('activo')->default(true);
                $table->timestamps();
            });
        }

        // 3. Consumo Diario de Alimento por Corral / Lote
        if (!Schema::hasTable('consumos_alimento')) {
            Schema::create('consumos_alimento', function (Blueprint $table) {
                $table->id();
                $table->foreignId('corral_lote_id')->constrained('corrales_lotes')->onDelete('cascade');
                $table->foreignId('racion_id')->constrained('raciones_alimento')->onDelete('cascade');
                $table->date('fecha');
                $table->decimal('kilos_servidos', 10, 2);
                $table->decimal('kilos_rechazo', 10, 2)->default(0); // Sobrante en comedero
                $table->decimal('kilos_consumidos', 10, 2); // Servidos - Rechazo
                $table->decimal('costo_total_mxn', 12, 2); // En Pesos Mexicanos
                $table->integer('cabezas_contadas')->default(1);
                $table->decimal('consumo_promedio_cabeza_kg', 8, 2)->default(0);
                $table->text('observaciones')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['corral_lote_id', 'fecha']);
            });
        }

        // 4. Agregar relaciones en la tabla Ganado
        Schema::table('ganado', function (Blueprint $table) {
            if (!Schema::hasColumn('ganado', 'corral_lote_id')) {
                $table->foreignId('corral_lote_id')->nullable()->after('lote_potrero')->constrained('corrales_lotes')->nullOnDelete();
            }
            if (!Schema::hasColumn('ganado', 'condicion_corporal')) {
                $table->decimal('condicion_corporal', 3, 1)->nullable()->after('ultimoPeso'); // Escala 1.0 a 9.0
            }
            if (!Schema::hasColumn('ganado', 'dias_en_engorda')) {
                $table->integer('dias_en_engorda')->default(0)->after('condicion_corporal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ganado', function (Blueprint $table) {
            if (Schema::hasColumn('ganado', 'corral_lote_id')) {
                $table->dropForeign(['corral_lote_id']);
                $table->dropColumn('corral_lote_id');
            }
            if (Schema::hasColumn('ganado', 'condicion_corporal')) {
                $table->dropColumn('condicion_corporal');
            }
            if (Schema::hasColumn('ganado', 'dias_en_engorda')) {
                $table->dropColumn('dias_en_engorda');
            }
        });

        Schema::dropIfExists('consumos_alimento');
        Schema::dropIfExists('raciones_alimento');
        Schema::dropIfExists('corrales_lotes');
    }
};
