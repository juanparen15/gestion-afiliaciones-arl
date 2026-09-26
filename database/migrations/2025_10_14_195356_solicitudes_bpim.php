<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_bpim', function (Blueprint $table) {
            $table->id();

            // Campos adicionales del formulario (en orden)
            $table->boolean('autorizacion_uso_datos')->default(false);

            // Información básica
            $table->string('codigo')->unique()->nullable();
            $table->string('consecutivo')->nullable();
            $table->string('dependencia');
            $table->string('cartera')->nullable();
            $table->string('numero_item_presupuesto')->nullable();

            // Información del solicitante
            $table->string('nombre_solicitante');
            $table->string('correo_solicitante');
            $table->string('correo_notificacion')->nullable();

            // Información del proyecto
            $table->string('nombre_proyecto');
            $table->string('codigo_bpim')->nullable();
            $table->string('codigo_bpin')->nullable();
            $table->text('objeto');

            // Información financiera
            $table->string('cdp')->nullable();
            $table->decimal('valor_cdp', 15, 2)->nullable();
            $table->decimal('valor_ep', 15, 2)->nullable();
            $table->string('objeto_gasto')->nullable();
            $table->string('fuente_recursos')->nullable();
            $table->string('otra_fuente_recurso')->nullable();

            // Clasificación presupuestal
            $table->string('mga')->nullable();
            $table->string('cpc')->nullable();
            $table->string('sector')->nullable();
            $table->string('programa')->nullable();
            $table->string('subprograma')->nullable();

            // Plan de Desarrollo Municipal (PDM)
            $table->string('pdm_sector')->nullable();
            $table->string('pdm_programa')->nullable();
            $table->string('pdm_producto')->nullable();

            // Plan de Desarrollo Nacional (PDN)
            $table->string('pdn_sector')->nullable();
            $table->string('pdn_programa')->nullable();
            $table->string('pdn_subprograma')->nullable();

            // Información adicional
            $table->string('tiempo_contractual')->nullable();
            $table->string('codigo_acta_necesidad')->nullable();
            $table->string('nombre_elabora')->nullable();

            // Estados y control
            $table->boolean('aprobar')->default(false);
            $table->boolean('aprobado')->default(false);
            $table->boolean('aprobado_enviado')->default(false);
            $table->boolean('rechazado')->default(false);
            $table->boolean('rechazo_enviado')->default(false);
            $table->boolean('check')->default(false);
            $table->text('motivo_rechazo')->nullable();

            // Archivos generados
            $table->string('url_documento_word')->nullable();
            $table->string('url_documento_pdf')->nullable();
            $table->string('ruta_carpeta')->nullable();

            // Fechas
            $table->timestamp('fecha_generacion')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();
            $table->timestamp('fecha_rechazo')->nullable();
            $table->timestamp('fecha_envio')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('codigo');
            $table->index('dependencia');
            $table->index('nombre_solicitante');
            $table->index('aprobado');
            $table->index('rechazado');

            $table->timestamp('fecha_modificacion_word')->nullable();
            $table->timestamp('fecha_generacion_pdf')->nullable();
            $table->string('usuario_ultima_edicion')->nullable();

            $table->string('usuario_elaboro')->nullable();
            $table->string('usuario_aprobo')->nullable();
            $table->timestamp('fecha_aprobacion_secretario')->nullable();

        });

        // Tabla para items de presupuesto (relación uno a muchos)
        Schema::create('presupuesto_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_bpim_id')
                ->constrained('solicitudes_bpim')
                ->onDelete('cascade');
            $table->text('descripcion');
            $table->decimal('valor_unitario', 15, 2)->nullable();
            $table->integer('meses')->nullable();
            $table->decimal('valor_total', 15, 2)->nullable();
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->index('solicitud_bpim_id');
            $table->index('orden');
        });
    }

    // public function down(): void
    // {
    //     Schema::dropIfExists('presupuesto_items');
    //     Schema::dropIfExists('solicitudes_bpim');
    // }

    public function down(): void
    {
        Schema::dropIfExists('presupuesto_items');
        Schema::dropIfExists('solicitudes_bpim');
        // Schema::table('solicitudes_bpim', function (Blueprint $table) {
        //     $table->dropColumn([
        //         'fecha_modificacion_word',
        //         'fecha_generacion_pdf',
        //         'usuario_ultima_edicion'
        //     ]);
        // });
    }
};
