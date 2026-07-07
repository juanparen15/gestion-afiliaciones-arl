<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas_necesidad', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('consecutivo')->nullable()->unique()
                ->comment('No. de Acta asignado al aprobar');

            $table->string('email_solicitante')->nullable();

            $table->foreignId('dependencia_id')->nullable()->constrained('dependencias')->nullOnDelete();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->string('dependencia_nombre')->nullable();
            $table->string('area_nombre')->nullable();

            $table->string('nombre_solicitante')->nullable();
            $table->text('objeto_contrato')->nullable();
            $table->string('tipo_contrato')->nullable();
            $table->string('duracion')->nullable();
            $table->string('modalidad_seleccion')->nullable();
            $table->text('tipo_solicitud')->nullable();
            $table->string('numero_contrato_convenio')->nullable();
            $table->decimal('presupuesto_oficial', 18, 2)->nullable();
            $table->string('codigo_bpim_bpin')->nullable();
            $table->string('codigo_paa')->nullable();
            $table->text('observaciones')->nullable();
            $table->string('nombre_completo')->nullable()->comment('Nombre de quien avala/firma');

            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('motivo_rechazo')->nullable();

            $table->timestamp('fecha_solicitud')->nullable();
            $table->timestamp('fecha_generado')->nullable();
            $table->timestamp('fecha_aprobado')->nullable();

            $table->string('pdf_path')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas_necesidad');
    }
};
