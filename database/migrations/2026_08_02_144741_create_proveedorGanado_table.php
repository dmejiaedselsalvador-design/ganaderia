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
        Schema::create('proveedorGanado', function (Blueprint $table) {
            $table->id();
            $table->string('nombreProveedor'); // Nombre del proveedor o rancho proveedor
            $table->string('estado')->default('activo');
            $table->string('nombreContacto')->nullable(); // Persona de contacto
            $table->string('telefono')->nullable(); // Teléfono
            $table->string('lugar')->nullable(); // Ciudad o región
            $table->text('ubicacion')->nullable();
            $table->string('razon_social')->nullable(); // Dirección opcional
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedorGanado');
    }
};
