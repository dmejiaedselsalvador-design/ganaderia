<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bitácora de correcciones: cada vez que se corrige o anula un registro capturado
 * (pesaje, servido, tratamiento, baja, venta o datos de compra) queda quién, cuándo, por qué y qué cambió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('tipo', 20)->index();        // pesaje | servido | tratamiento | baja | venta | compra
            $table->string('registro_id', 40);
            $table->string('accion', 20);                // corregido | anulado | revertido
            $table->string('descripcion', 255);
            $table->string('motivo', 500);
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->unsignedBigInteger('ganado_id')->nullable()->index();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correcciones');
    }
};
