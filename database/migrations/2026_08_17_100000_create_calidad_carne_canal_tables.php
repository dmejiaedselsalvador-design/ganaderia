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
        if (!Schema::hasTable('evaluaciones_canal')) {
            Schema::create('evaluaciones_canal', function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('ganado_id')->nullable();
                $table->foreign('ganado_id')
                      ->references('GanadoID')
                      ->on('ganado')
                      ->nullOnDelete();

                $table->string('arete_snapshot', 50); // Arete del animal
                $table->string('folio_rastro_canal', 50)->unique(); // Ej. CANAL-2026-0001
                $table->date('fecha_faena');
                $table->string('planta_o_rastro', 150)->nullable(); // Ej. Rastro TIF, Frigorífico

                // Rendimientos en Báscula y Canal
                $table->decimal('peso_vivo_bascula_kg', 8, 2); // Peso en pie antes de faena
                $table->decimal('peso_canal_caliente_kg', 8, 2); // PCC recién desposte
                $table->decimal('rendimiento_canal_caliente_pct', 5, 2); // (PCC / Peso vivo) * 100
                $table->decimal('peso_canal_fria_kg', 8, 2)->nullable(); // PCF tras cámara 24h
                $table->decimal('merma_enfriamiento_pct', 5, 2)->default(2.00); // Merma de oreo (~2%)
                $table->decimal('rendimiento_canal_fria_pct', 5, 2)->nullable(); // (PCF / Peso vivo) * 100

                // Parámetros de Calidad de Carne (Dra. Salud Rubio / Engormix & NMX-FF-078-SCFI)
                $table->enum('marmoleo', [
                    'Abundante',
                    'Moderado',
                    'Pequeno',
                    'Ligero',
                    'Practicamente_Carente'
                ])->default('Moderado'); // Grasa intramuscular

                $table->decimal('espesor_grasa_dorsal_mm', 5, 2)->default(6.50); // Grasa de cobertura (mm)
                $table->decimal('area_ojo_costilla_cm2', 6, 2)->nullable(); // Ribeye Area (cm²)

                $table->enum('color_carne', [
                    'Rojo_Cereza_Brillante',
                    'Rojo_Medio',
                    'Rojo_Oscuro_DFD'
                ])->default('Rojo_Cereza_Brillante');

                $table->enum('color_grasa', [
                    'Blanco_Nieve',
                    'Blanco_Cremoso',
                    'Ligeramente_Amarillo',
                    'Amarillo'
                ])->default('Blanco_Cremoso');

                // pH Muscular a las 24 horas (Parámetro crítico Dra. Salud Rubio)
                // 5.4 - 5.7 = Óptimo normal. > 5.8 = Riesgo de carne oscura, firme y seca (DFD)
                $table->decimal('ph_24h', 4, 2)->default(5.55);

                // Clasificación Oficial según Norma Mexicana NMX-FF-078-SCFI
                $table->enum('clasificacion_mexicana', [
                    'Suprema',
                    'Selecta',
                    'Estandar',
                    'Comercial'
                ])->default('Selecta');

                // Valuación Comercial en Pesos Mexicanos (MXN)
                $table->decimal('precio_canal_por_kg_mxn', 10, 2)->default(0);
                $table->decimal('valor_total_canal_mxn', 12, 2)->default(0);
                $table->string('destino_comercial', 150)->nullable(); // Boutiques, Tiendas, Canal Caliente
                $table->text('observaciones_tecnicas')->nullable();

                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->index(['ganado_id', 'fecha_faena']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_canal');
    }
};
