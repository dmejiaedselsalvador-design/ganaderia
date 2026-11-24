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
        if (!Schema::hasTable('tratamientos_sanitarios')) {
            Schema::create('tratamientos_sanitarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ganado_id');
                $table->foreign('ganado_id')
                      ->references('GanadoID')
                      ->on('ganado')
                      ->onDelete('cascade');

                $table->enum('tipo_evento', [
                    'Recepcion',
                    'Vacunacion',
                    'Desparasitacion',
                    'Vitamina',
                    'Antibiotico',
                    'Implante',
                    'Tratamiento_Clinico'
                ])->default('Recepcion');

                $table->string('medicamento_producto', 150); // Ej. Bovilis Vista Once, Dectomax, Catosal B12, Revalor-S
                $table->string('dosis_aplicada', 50); // Ej. 5 ml, 1 implante
                $table->string('via_administracion', 50)->default('Subcutánea'); // Subcutánea, Intramuscular, Oral
                $table->date('fecha_aplicacion');
                $table->integer('dias_retiro_rastro')->default(0); // Tiempo de espera antes de faena
                $table->date('fecha_fin_retiro')->nullable(); // Fecha calculada
                $table->decimal('costo_medicamento_mxn', 10, 2)->default(0); // Pesos Mexicanos (MXN)
                $table->string('veterinario_responsable', 150)->nullable();
                $table->text('diagnostico_motivo')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();

                $table->index(['ganado_id', 'fecha_aplicacion']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tratamientos_sanitarios');
    }
};
