<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ubicaciones (ranchos/engordas), precios de mercado y metas por lote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 120);
            $table->string('clave_upp', 40)->nullable();      // UPP / PSG (SINIIGA)
            $table->string('municipio', 100)->nullable();
            $table->string('estado', 100)->nullable()->default('Sonora');
            $table->string('responsable', 120)->nullable();
            $table->text('direccion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('precios_mercado', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->string('categoria', 30);                   // Becerro, Torete, ... o "General"
            $table->decimal('precio_kg', 10, 2);               // MXN por kg en pie
            $table->string('fuente', 150)->nullable();         // Subasta, comprador, UGRS, SNIIM...
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['categoria', 'fecha']);
        });

        Schema::table('corrales_lotes', function (Blueprint $table) {
            $table->foreignId('ubicacion_id')->nullable()->after('id')->constrained('ubicaciones')->nullOnDelete();
            $table->decimal('meta_gdp', 6, 3)->nullable()->after('peso_promedio_objetivo');
            $table->decimal('meta_costo_kg', 10, 2)->nullable()->after('meta_gdp');
            $table->decimal('meta_mortalidad_pct', 5, 2)->nullable()->after('meta_costo_kg');
            $table->decimal('meta_conversion', 6, 2)->nullable()->after('meta_mortalidad_pct');
            $table->decimal('presupuesto_mxn', 14, 2)->nullable()->after('meta_conversion');
        });
    }

    public function down(): void
    {
        Schema::table('corrales_lotes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ubicacion_id');
            $table->dropColumn(['meta_gdp', 'meta_costo_kg', 'meta_mortalidad_pct', 'meta_conversion', 'presupuesto_mxn']);
        });
        Schema::dropIfExists('precios_mercado');
        Schema::dropIfExists('ubicaciones');
    }
};
