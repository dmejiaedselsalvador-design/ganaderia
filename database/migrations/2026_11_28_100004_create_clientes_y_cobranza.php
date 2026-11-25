<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('razon_social', 200)->nullable();
            $table->string('rfc', 13)->nullable()->index();
            $table->string('regimen_fiscal', 5)->nullable();       // clave SAT, ej. 601, 612, 626
            $table->string('uso_cfdi', 5)->nullable();             // G01, G03...
            $table->string('codigo_postal', 5)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('direccion')->nullable();
            $table->string('contacto', 150)->nullable();
            $table->decimal('limite_credito', 14, 2)->default(0);
            $table->unsignedSmallInteger('dias_credito')->default(0);
            $table->boolean('activo')->default(true);
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('ventas_ganado', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->after('folio_venta')->constrained('clientes')->nullOnDelete();
            $table->string('condicion_pago', 10)->default('contado')->after('modalidad_pago'); // contado | credito
            $table->string('forma_pago', 30)->nullable()->after('condicion_pago');            // efectivo, transferencia, cheque...
            $table->date('fecha_vencimiento')->nullable()->after('forma_pago');
            $table->decimal('saldo_pendiente', 14, 2)->default(0)->after('total_venta');
            $table->string('folio_cfdi', 60)->nullable()->after('saldo_pendiente');
        });

        Schema::create('abonos_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas_ganado')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('monto', 14, 2);
            $table->string('forma_pago', 30);
            $table->string('referencia', 100)->nullable();       // núm. de transferencia / cheque
            $table->string('estado', 15)->default('vigente');     // vigente | anulado
            $table->string('motivo_anulacion', 255)->nullable();
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abonos_venta');
        Schema::table('ventas_ganado', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
            $table->dropColumn(['condicion_pago', 'forma_pago', 'fecha_vencimiento', 'saldo_pendiente', 'folio_cfdi']);
        });
        Schema::dropIfExists('clientes');
    }
};
