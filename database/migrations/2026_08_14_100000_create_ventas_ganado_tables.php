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
        if (!Schema::hasTable('ventas_ganado')) {
            Schema::create('ventas_ganado', function (Blueprint $table) {
                $table->id();
                $table->string('folio_venta', 50)->unique(); // Ej. VTA-202611-0001
                $table->string('cliente_nombre', 255);
                $table->string('cliente_telefono', 50)->nullable();
                $table->string('cliente_identificacion', 100)->nullable(); // RFC o INE
                $table->string('destino', 255)->nullable(); // Rastro, engorda, exportación
                $table->date('fecha_venta');
                $table->enum('modalidad_pago', ['por_kilo', 'precio_fijo'])->default('por_kilo');

                $table->integer('total_cabezas')->default(1);
                $table->decimal('peso_total_kg', 10, 2)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('descuento', 12, 2)->default(0);
                $table->decimal('total_venta', 12, 2)->default(0);

                $table->enum('estado', ['completada', 'anulada'])->default('completada');
                $table->text('notas')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

                $table->timestamps();
            });
        }

        if (!Schema::hasTable('detalle_ventas_ganado')) {
            Schema::create('detalle_ventas_ganado', function (Blueprint $table) {
                $table->id();
                $table->foreignId('venta_id')->constrained('ventas_ganado')->onDelete('cascade');

                $table->unsignedBigInteger('ganado_id');
                $table->foreign('ganado_id')
                      ->references('GanadoID')
                      ->on('ganado')
                      ->onDelete('cascade');

                $table->string('arete_snapshot', 50); // Snapshot del arete al momento de vender
                $table->string('categoria_snapshot', 50);
                $table->string('raza_snapshot', 100)->nullable();
                $table->decimal('peso_venta', 8, 2); // Peso en báscula al salir
                $table->decimal('precio_unitario', 10, 2); // Precio por kg o por cabeza
                $table->decimal('subtotal', 12, 2); // Total para este animal

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas_ganado');
        Schema::dropIfExists('ventas_ganado');
    }
};
