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
        Schema::table('ganado', function (Blueprint $table) {
            if (!Schema::hasColumn('ganado', 'peso_objetivo')) {
                $table->decimal('peso_objetivo', 8, 2)->nullable()->after('ultimoPeso');
            }
            if (!Schema::hasColumn('ganado', 'lote_potrero')) {
                $table->string('lote_potrero', 100)->nullable()->after('categoria');
            }
            if (!Schema::hasColumn('ganado', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('fechaCompra');
            }
            if (!Schema::hasColumn('ganado', 'madre_arete')) {
                $table->string('madre_arete', 100)->nullable()->after('fecha_nacimiento');
            }
            if (!Schema::hasColumn('ganado', 'padre_arete')) {
                $table->string('padre_arete', 100)->nullable()->after('madre_arete');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ganado', function (Blueprint $table) {
            $table->dropColumn([
                'peso_objetivo',
                'lote_potrero',
                'fecha_nacimiento',
                'madre_arete',
                'padre_arete',
            ]);
        });
    }
};
