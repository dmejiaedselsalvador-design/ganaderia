<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peso objetivo de venta por categoría (Becerro, Torete, Vaquilla…), configurable desde el inventario.
 * Tabla nueva: no modifica datos existentes. Si una categoría no tiene registro se usa el valor de fábrica.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesos_objetivo', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 30)->unique();
            $table->decimal('peso_kg', 8, 2);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesos_objetivo');
    }
};
