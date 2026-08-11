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
        Schema::create('adelantos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedorGanado')->onDelete('cascade'); // Proveedor
            $table->text('concepto'); // Tipo de adelanto
            $table->decimal('dinero', 12, 2); // Si es dinero, el monto; si es concentrado, el valor equivalente en dinero o la cantida
            $table->decimal('montoDisponible', 12, 2);
            $table->date('date'); // Fecha del adelanto
            $table->enum('status', ['disponible', 'parcial', 'agotado'])->default('disponible');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adelantos');
    }
};
