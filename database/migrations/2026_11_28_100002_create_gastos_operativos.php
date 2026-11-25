<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gastos_operativos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->string('categoria', 40)->index();          // Mano de obra, Combustible, ...
            $table->string('concepto', 255);
            $table->decimal('monto', 14, 2);
            $table->string('proveedor', 150)->nullable();
            $table->string('forma_pago', 30)->nullable();
            $table->string('folio_cfdi', 60)->nullable();      // UUID / folio de la factura CFDI
            // Si se asigna a un corral el costo va a ese lote; si no, se reparte entre todo el hato
            $table->foreignId('corral_lote_id')->nullable()->constrained('corrales_lotes')->nullOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones')->nullOnDelete();
            $table->string('estado', 15)->default('vigente');  // vigente | anulado
            $table->string('motivo_anulacion', 255)->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gastos_operativos');
    }
};
