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
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
           // Relación opcional con proveedores (si se compró a alguien)
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();

            // Identificación del animal
            $table->string('tag_number')->unique(); // Número de arete oficial (ej. SINIIGA o local)
            $table->string('breed')->nullable(); // Raza (ej. Angus, Hereford, Brahman, Cruzado)
            $table->enum('gender', ['Macho', 'Hembra']); // Sexo

            // Métricas y pesos (Guardados como números puros)
            $table->decimal('birth_weight', 8, 2)->nullable(); // Peso al nacer o ingreso en kg
            $table->decimal('current_weight', 8, 2); // Último peso registrado en báscula (kg)
            $table->decimal('purchase_price', 10, 2)->nullable(); // Costo de compra en pesos (MXN)

            // Fechas y Estado
            $table->date('purchase_date')->nullable(); // Fecha de adquisición
            $table->enum('status', ['Activo', 'Vendido', 'Muerto', 'Exportado'])->default('Activo'); // Estado actual
            $table->text('notes')->nullable(); // Observaciones médicas o generales

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
