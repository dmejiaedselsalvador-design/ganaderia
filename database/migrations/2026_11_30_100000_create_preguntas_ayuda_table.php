<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Preguntas que los usuarios envían desde el botón de Ayuda (?) de cada pantalla,
 * con captura de lo que estaban viendo. Las responde un administrador dentro del sistema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_ayuda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('planta', 30)->nullable();
            $table->string('ruta', 120)->nullable();          // nombre de la ruta (pantalla)
            $table->string('pantalla', 150)->nullable();      // título legible
            $table->string('url', 500)->nullable();
            $table->text('pregunta');
            $table->string('captura_path')->nullable();       // archivo privado (storage/app/private/ayuda)
            $table->string('navegador', 255)->nullable();
            $table->string('estado', 20)->default('pendiente'); // pendiente | respondida | cerrada
            $table->text('respuesta')->nullable();
            $table->foreignId('respondida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('respondida_at')->nullable();
            $table->timestamp('respuesta_vista_at')->nullable(); // el usuario ya leyó la respuesta
            $table->timestamps();

            $table->index(['estado', 'created_at']);
            $table->index(['user_id', 'respuesta_vista_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_ayuda');
    }
};
