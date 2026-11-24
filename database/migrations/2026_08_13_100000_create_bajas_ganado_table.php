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
        if (!Schema::hasTable('bajas_ganado')) {
            Schema::create('bajas_ganado', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ganado_id')->unique();
                $table->foreign('ganado_id')
                      ->references('GanadoID')
                      ->on('ganado')
                      ->onDelete('cascade');

                $table->date('fecha_muerte');
                $table->string('causa_muerte', 150); // Ej. Neumonía, Timpanismo, Traumatismo, Mordedura, Desconocida
                $table->text('necropsia')->nullable(); // Hallazgos veterinarios
                $table->decimal('ultimo_peso', 8, 2)->nullable(); // Último peso antes de la muerte
                $table->text('observaciones')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas_ganado');
    }
};
