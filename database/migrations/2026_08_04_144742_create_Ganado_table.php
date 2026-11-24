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
        Schema::create('ganado', function (Blueprint $table) {
            $table->id('GanadoID');
           // Relación opcional con proveedores (si se compró a alguien)
          //  $table->foreignId('proveedor_id')->nullable()->constrained('proveedorGanado')->nullOnDelete();

            // Identificación del animal
            $table->string('areteID')->unique(); // Número de arete oficial (ej. SINIIGA o local)
            $table->string('raza')->nullable(); // Raza (ej. Angus, Hereford, Brahman, Cruzado)
            $table->enum('categoria', ['Becerro', 'Becerra', 'Vaca', 'Vaquilla', 'Toro', 'Torete']);

    // El sexo se llena solo en la base de datos
    $table->enum('sexo', ['Macho', 'Hembra']);

            // Métricas y pesos (Guardados como números puros)
            $table->decimal('pesoActual', 8, 2)->nullable(); // Peso al nacer o ingreso en kg
            $table->decimal('ultimoPeso', 8, 2); // Último peso registrado en báscula (kg)
            $table->decimal('precioCompra', 10, 2)->nullable(); // Costo de compra en pesos (MXN)
            $table->decimal('precioGanadoTotal', 12, 2)->storedAs('ultimoPeso * precioCompra')->nullable();
            // Fechas y Estado
            $table->date('fechaCompra')->nullable(); // Fecha de adquisición
            $table->enum('status', ['Activo', 'Vendido', 'Muerto', 'Exportado'])->default('Activo'); // Estado actual
            $table->text('notas')->nullable(); // Observaciones médicas o generales
            $table->string('foto')->nullable();
$table->foreignId('facturaID')->nullable()->constrained('facturaGanado')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ganado');
    }
};
