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
            $table->foreignId('proveedor_id')->constrained('proveedorganado')->onDelete('cascade'); // Proveedor
            $table->text('concepto'); // Tipo de adelanto
            $table->decimal('dinero', 12, 2); // Si es dinero, el monto; si es concentrado, el valor equivalente en dinero o la cantidad
            $table->date('date'); // Fecha del adelanto
            $table->boolean('is_used')->default(false); // Para saber si ya se cruzó con un lote
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
