<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'tema')) {
                $table->string('tema', 10)->default('oscuro')->after('status');
            }
            if (! Schema::hasColumn('users', 'last_division')) {
                $table->string('last_division', 40)->nullable()->after('tema');
            }
        });

        $roles = config('permission.table_names.roles', 'roles');
        Schema::table($roles, function (Blueprint $table) use ($roles) {
            if (! Schema::hasColumn($roles, 'nivel')) {
                // Jerarquía: un usuario solo puede asignar roles de nivel inferior al suyo.
                $table->unsignedSmallInteger('nivel')->default(10)->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'tema', 'last_division']);
        });

        Schema::table(config('permission.table_names.roles', 'roles'), function (Blueprint $table) {
            $table->dropColumn('nivel');
        });
    }
};
