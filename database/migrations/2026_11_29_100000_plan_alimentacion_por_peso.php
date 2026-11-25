<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dieta + BOM + bodega basada en pesos:
 *  - Asignación de dieta por corral con historial.
 *  - Rango de consumo (% de peso vivo) por dieta y su base (materia seca o tal como se ofrece).
 *  - % de materia seca y factor de conversión a kg por insumo.
 *  - Índice para obtener rápido el último pesaje de cada animal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignaciones_dieta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('corral_lote_id')->constrained('corrales_lotes')->cascadeOnDelete();
            $table->foreignId('racion_id')->constrained('raciones_alimento')->restrictOnDelete();
            $table->decimal('pct_pv', 5, 2)->nullable();        // % de peso vivo propio del corral (null = el sugerido de la dieta)
            $table->date('desde');
            $table->date('hasta')->nullable();                   // null = vigente
            $table->string('notas', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['corral_lote_id', 'hasta']);
        });

        Schema::table('raciones_alimento', function (Blueprint $table) {
            $table->string('base_consumo', 3)->default('MS')->after('energia_metabolizable_mcal'); // MS | TCO
            $table->decimal('pct_pv_min', 5, 2)->nullable()->after('base_consumo');
            $table->decimal('pct_pv_max', 5, 2)->nullable()->after('pct_pv_min');
            $table->decimal('pct_pv_sugerido', 5, 2)->nullable()->after('pct_pv_max');
        });

        // Rangos típicos de consumo de materia seca por fase (editables en cada dieta)
        foreach ([
            'Recepcion'     => [1.8, 2.5, 2.2],
            'Crecimiento'   => [2.3, 2.9, 2.6],
            'Finalizacion'  => [2.5, 3.2, 2.8],
            'Mantenimiento' => [1.8, 2.3, 2.0],
        ] as $fase => [$min, $max, $sug]) {
            DB::table('raciones_alimento')->where('fase', $fase)
                ->update(['pct_pv_min' => $min, 'pct_pv_max' => $max, 'pct_pv_sugerido' => $sug]);
        }

        Schema::table('insumos', function (Blueprint $table) {
            $table->decimal('materia_seca_pct', 5, 2)->nullable()->after('unidad');
            $table->decimal('factor_kg', 12, 3)->default(1)->after('materia_seca_pct'); // kg que contiene 1 unidad (ton = 1000, saco = 40)
        });
        DB::table('insumos')->where('unidad', 'ton')->update(['factor_kg' => 1000]);

        Schema::table('pesajes_ganado', function (Blueprint $table) {
            $table->index(['ganado_id', 'fecha_pesaje'], 'pesajes_ganado_fecha_idx');
        });
    }

    public function down(): void
    {
        Schema::table('pesajes_ganado', fn (Blueprint $t) => $t->dropIndex('pesajes_ganado_fecha_idx'));
        Schema::table('insumos', fn (Blueprint $t) => $t->dropColumn(['materia_seca_pct', 'factor_kg']));
        Schema::table('raciones_alimento', fn (Blueprint $t) => $t->dropColumn(['base_consumo', 'pct_pv_min', 'pct_pv_max', 'pct_pv_sugerido']));
        Schema::dropIfExists('asignaciones_dieta');
    }
};
