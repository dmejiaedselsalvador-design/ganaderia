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
        Schema::create('adelanto_factura', function (Blueprint $table) {
           $table->id();
    $table->foreignId('adelanto_id')->constrained('adelantos')->onDelete('cascade');
    $table->foreignId('factura_id')->constrained('facturaGanado')->onDelete('cascade');
    $table->decimal('montoAplicado', 12, 2); // Cuánto dinero de este adelanto específico se usó en esta factura
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adelanto_factura');
    }
};
