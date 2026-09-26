<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_verificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_bpim_id')->constrained('solicitudes_bpim')->onDelete('cascade');
            $table->string('codigo_verificacion', 64)->unique(); // Código único para el QR
            $table->string('hash_documento', 64); // Hash SHA-256 del PDF
            $table->timestamp('fecha_firma');
            $table->string('firmado_por');
            $table->string('ip_firma', 45)->nullable();
            $table->text('metadata')->nullable(); // JSON con info adicional
            $table->integer('veces_verificado')->default(0);
            $table->timestamp('ultima_verificacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('codigo_verificacion');
            $table->index('hash_documento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_verificacion');
    }
};