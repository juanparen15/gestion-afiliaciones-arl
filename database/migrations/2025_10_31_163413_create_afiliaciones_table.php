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
        Schema::create('afiliaciones', function (Blueprint $table) {
            $table->id();

            // Información del contratista
            $table->string('nombre_contratista');
            $table->enum('tipo_documento', ['CC', 'CE', 'PP', 'TI', 'NIT'])->default('CC');
            $table->string('numero_documento')->unique();
            $table->string('email_contratista')->nullable();
            $table->string('telefono_contratista')->nullable();

            // Información del contrato
            $table->string('numero_contrato');
            $table->text('objeto_contractual');
            $table->decimal('valor_contrato', 15, 2);
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->string('supervisor_contrato')->nullable();

            // Información de la ARL
            $table->string('nombre_arl');
            $table->enum('tipo_riesgo', ['I', 'II', 'III', 'IV', 'V'])->default('I');
            $table->string('numero_afiliacion_arl')->nullable();
            $table->date('fecha_afiliacion_arl')->nullable();

            // Relaciones
            $table->foreignId('dependencia_id')->constrained('dependencias')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');

            // Estado y validación
            $table->enum('estado', ['pendiente', 'validado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->text('motivo_rechazo')->nullable();
            $table->timestamp('fecha_validacion')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('numero_documento');
            $table->index('numero_contrato');
            $table->index('estado');
            $table->index('dependencia_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliaciones');
    }
};
