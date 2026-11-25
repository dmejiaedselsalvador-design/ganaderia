<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_clave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            // Hash del token que guarda el navegador del solicitante (cookie). Solo él puede ver la clave.
            $table->string('token_hash', 64)->unique();
            $table->string('estado', 20)->default('pendiente')->index(); // pendiente|aprobada|entregada|denegada
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            // Clave temporal cifrada; se borra en cuanto el usuario la ve.
            $table->text('clave_cifrada')->nullable();
            $table->foreignId('atendida_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('atendida_at')->nullable();
            $table->timestamp('entregada_at')->nullable();
            $table->string('motivo', 255)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'bloqueo_solicitud_clave')) {
                // true = el usuario ya no puede pedir cambios de clave desde el login
                $table->boolean('bloqueo_solicitud_clave')->default(false)->after('password_changed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes_clave');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bloqueo_solicitud_clave');
        });
    }
};
