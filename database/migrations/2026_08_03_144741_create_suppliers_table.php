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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del proveedor o rancho proveedor
            $table->string('contact_name')->nullable(); // Persona de contacto
            $table->string('phone')->nullable(); // Teléfono
            $table->string('city')->nullable(); // Ciudad o región
            $table->text('address')->nullable();
            $table->string('razon_social')->nullable(); // Dirección opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
