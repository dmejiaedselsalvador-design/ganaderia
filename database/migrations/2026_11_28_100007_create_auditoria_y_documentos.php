<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bitácora: quién hizo qué, cuándo y qué cambió
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 20)->index();                 // creado | actualizado | eliminado | restaurado
            $table->string('modelo', 120)->index();
            $table->string('modelo_id', 40)->nullable()->index();
            $table->string('descripcion', 255)->nullable();
            $table->json('antes')->nullable();
            $table->json('despues')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });

        // Documentos adjuntos (guías REEMO, certificados zoosanitarios, CFDI, comprobantes...)
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('documentable_type', 120);
            $table->string('documentable_id', 40);
            $table->string('tipo', 40);
            $table->string('folio', 100)->nullable();
            $table->date('fecha')->nullable();
            $table->string('ruta');
            $table->string('nombre_original');
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('tamano')->default(0);
            $table->text('notas')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['documentable_type', 'documentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
        Schema::dropIfExists('auditoria');
    }
};
