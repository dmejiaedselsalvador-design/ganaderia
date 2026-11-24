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
        if (!Schema::hasTable('pesajes_ganado')) {
            Schema::create('pesajes_ganado', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ganado_id');
                $table->foreign('ganado_id')
                      ->references('GanadoID')
                      ->on('ganado')
                      ->onDelete('cascade');

                $table->decimal('peso', 8, 2); // Peso en kg
                $table->date('fecha_pesaje'); // Fecha del pesaje
                $table->decimal('peso_anterior', 8, 2)->nullable();
                $table->decimal('ganancia_peso', 8, 2)->nullable(); // Delta kg respecto al pesaje anterior
                $table->integer('dias_transcurridos')->nullable(); // Días desde el pesaje anterior
                $table->decimal('gdp', 6, 3)->nullable(); // Ganancia Diaria de Peso (kg/día)

                $table->string('bascula', 100)->nullable(); // Báscula de corral o portátil
                $table->string('lote_corral', 100)->nullable(); // Corral al momento de pesar
                $table->text('observaciones')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();

                $table->index(['ganado_id', 'fecha_pesaje']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesajes_ganado');
    }
};
