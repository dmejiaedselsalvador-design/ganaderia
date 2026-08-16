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
        Schema::create('facturaGanado', function (Blueprint $table) {
         $table->id();
         $table->foreignId('proveedorID')->constrained('proveedorGanado')->cascadeOnDelete();
         $table->date('fechaFactura');
         $table->string('numeroFactura')->unique(); // Número de factura o recibo
         $table->text('notas')->nullable();
         $table->enum('estado',['pendiente','pagada','proceso','parcial','anulada'])->default('pendiente');
          $table->timestamps();
           });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturaGanado');
    }
};
