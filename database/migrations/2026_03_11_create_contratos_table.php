<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();

            // --- IDENTIFICACIÓN ---
            $table->integer('numero_contrato')->nullable();
            $table->string('vigencia', 4)->nullable()->index();
            $table->string('id_contrato_secop', 60)->nullable()->unique();
            $table->string('numero_constancia_secop', 60)->nullable();
            $table->string('estado', 40)->nullable()->index();
            $table->string('tipo', 40)->nullable();
            $table->string('tipo_contrato', 60)->nullable();
            $table->string('clase', 40)->nullable();
            $table->string('modalidad', 80)->nullable();
            $table->string('modalidad_seleccion', 120)->nullable();
            $table->string('misional_apoyo', 60)->nullable();
            $table->string('profesional_encargado', 120)->nullable();
            $table->date('fecha_ultima_modificacion')->nullable();

            // --- ENTIDAD ---
            $table->string('nit_entidad', 20)->nullable();
            $table->string('entidad', 150)->nullable();
            $table->string('unidad_ejecucion', 150)->nullable();

            // --- INTERVENTORÍA ---
            $table->string('cto_interventoria', 60)->nullable();
            $table->string('nombre_interventor', 150)->nullable();
            $table->string('direccion_interventor', 150)->nullable();
            $table->string('documento_interventor', 30)->nullable();

            // --- OBJETO Y FECHAS ---
            $table->text('objeto')->nullable();
            $table->date('fecha_contrato')->nullable();
            $table->date('fecha_aprobacion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_terminacion')->nullable();
            $table->integer('plazo_anos')->nullable();
            $table->integer('plazo_meses')->nullable();
            $table->integer('plazo_dias')->nullable();

            // --- ADICIÓN 1 ---
            $table->decimal('valor_adicional_1', 15, 2)->nullable();
            $table->date('fecha_adicional_1')->nullable();
            $table->integer('plazo_anos_adicional_1')->nullable();
            $table->integer('plazo_meses_adicional_1')->nullable();
            $table->integer('plazo_dias_adicional_1')->nullable();

            // --- ADICIÓN 2 ---
            $table->decimal('valor_adicional_2', 15, 2)->nullable();
            $table->date('fecha_adicional_2')->nullable();
            $table->integer('plazo_anos_adicional_2')->nullable();
            $table->integer('plazo_meses_adicional_2')->nullable();
            $table->integer('plazo_dias_adicional_2')->nullable();

            // --- ADICIÓN 3 ---
            $table->decimal('valor_adicional_3', 15, 2)->nullable();
            $table->date('fecha_adicional_3')->nullable();
            $table->integer('plazo_anos_adicional_3')->nullable();
            $table->integer('plazo_meses_adicional_3')->nullable();
            $table->integer('plazo_dias_adicional_3')->nullable();

            // --- FINANCIERO ---
            $table->decimal('valor_contrato', 15, 2)->nullable();

            // --- CDP ---
            $table->string('solicitud_cdp', 60)->nullable();
            $table->date('fecha_solicitud_cdp')->nullable();
            $table->string('numero_cdp', 40)->nullable();
            $table->date('fecha_cdp')->nullable();
            $table->decimal('valor_cdp', 15, 2)->nullable();

            // --- BPIN ---
            $table->string('solicitud_bpin', 60)->nullable();
            $table->string('codigo_bpim', 40)->nullable();
            $table->string('codigo_bpin', 40)->nullable();
            $table->string('nombre_proyecto', 200)->nullable();
            $table->string('sector', 80)->nullable();
            $table->string('programa', 100)->nullable();
            $table->string('subprograma', 100)->nullable();
            $table->string('dependencia_proyecto', 120)->nullable();
            $table->string('meta_plan_desarrollo', 150)->nullable();

            // --- MODALIDAD / TIPO ---
            $table->string('codigo_unspsc', 40)->nullable();
            $table->string('descripcion_unspsc', 150)->nullable();
            $table->string('segmento_servicio', 100)->nullable();
            $table->string('acta_ampliacion_plazo', 60)->nullable();
            $table->integer('dias_ampliacion')->nullable();
            $table->integer('total_dias_ampliacion')->nullable();
            $table->integer('numero_suspensiones')->nullable();
            $table->integer('dias_suspension')->nullable();
            $table->date('fecha_acta_reinicio')->nullable();

            // --- BIEN INMUEBLE ---
            $table->string('direccion_bien_inmueble', 150)->nullable();
            $table->string('matricula', 40)->nullable();
            $table->string('codigo_catastral', 40)->nullable();

            // --- CONTRATISTA PERSONA NATURAL ---
            $table->string('nombre_persona_natural', 150)->nullable();
            $table->string('genero', 20)->nullable();
            $table->string('cedula', 20)->nullable();
            $table->string('lugar_expedicion_cedula', 80)->nullable();
            $table->date('fecha_expedicion_cedula')->nullable();
            $table->string('lugar_nacimiento', 80)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('titulo_bachiller', 100)->nullable();
            $table->integer('ano_bachiller')->nullable();
            $table->string('titulo_profesional', 150)->nullable();
            $table->string('universidad', 150)->nullable();
            $table->string('perfil', 60)->nullable();
            $table->integer('ano_grado_profesional')->nullable();
            $table->string('especializaciones', 150)->nullable();
            $table->string('universidad_posgrado', 150)->nullable();
            $table->integer('ano_grado_posgrado')->nullable();
            $table->string('correo_contratista', 100)->nullable();

            // --- CONTRATISTA PERSONA JURÍDICA ---
            $table->string('nombre_persona_juridica', 150)->nullable();
            $table->string('nit_contratista', 20)->nullable();
            $table->integer('dv')->nullable();
            $table->string('direccion_contratista', 150)->nullable();
            $table->string('ciudad_contratista', 80)->nullable();
            $table->string('telefono_contratista', 40)->nullable();
            $table->string('entidad_bancaria', 80)->nullable();
            $table->string('tipo_cuenta_bancaria', 40)->nullable();
            $table->string('numero_cuenta_bancaria', 30)->nullable();

            // --- CONSORCIO / UNIÓN TEMPORAL ---
            $table->string('integrante_1_consorcio', 150)->nullable();
            $table->string('participacion_1', 20)->nullable();
            $table->string('tipo_doc_integrante_1', 20)->nullable();
            $table->string('doc_integrante_1', 30)->nullable();
            $table->string('direccion_integrante_1', 120)->nullable();
            $table->string('integrante_2_consorcio', 150)->nullable();
            $table->string('participacion_2', 20)->nullable();
            $table->string('tipo_doc_integrante_2', 20)->nullable();
            $table->string('doc_integrante_2', 30)->nullable();
            $table->string('direccion_integrante_2', 120)->nullable();
            $table->string('integrante_3_consorcio', 150)->nullable();
            $table->string('participacion_3', 20)->nullable();
            $table->string('tipo_doc_integrante_3', 20)->nullable();
            $table->string('doc_integrante_3', 30)->nullable();
            $table->string('direccion_integrante_3', 120)->nullable();

            // --- SUPERVISIÓN ---
            $table->string('dependencia_contrato', 150)->nullable()->index();
            $table->string('asignado_supervision', 100)->nullable();
            $table->string('tipo_supervision', 60)->nullable();
            $table->string('identificacion_supervisor', 20)->nullable();
            $table->string('titulo_supervisor', 100)->nullable();
            $table->string('nombre_supervisor', 150)->nullable();
            $table->string('cargo_supervisor', 150)->nullable();
            $table->string('tipo_vinculacion_supervisor', 80)->nullable();
            $table->string('oficina_supervisor', 150)->nullable();
            $table->date('fecha_designacion_supervision')->nullable();

            // --- PÓLIZAS ---
            $table->string('acta_aprobacion_poliza', 60)->nullable();
            $table->date('fecha_aprobacion_poliza')->nullable();
            $table->string('compannia_aseguradora', 120)->nullable();
            $table->string('nit_aseguradora', 20)->nullable();
            $table->string('poliza_cumplimiento', 60)->nullable();
            $table->string('anexo_cumplimiento', 60)->nullable();
            $table->date('fecha_expedicion_poliza_cumplimiento')->nullable();
            $table->string('vigencia_cumplimiento', 60)->nullable();
            $table->string('vigencia_pago_anticipado', 60)->nullable();
            $table->string('vigencia_pago_salarios', 60)->nullable();
            $table->string('vigencia_calidad_servicio', 60)->nullable();
            $table->string('poliza_responsabilidad', 60)->nullable();
            $table->string('anexo_responsabilidad', 60)->nullable();
            $table->date('fecha_expedicion_poliza_responsabilidad')->nullable();
            $table->string('vigencia_responsabilidad', 60)->nullable();

            // --- CRP / RECURSOS ---
            $table->string('numero_crp', 40)->nullable();
            $table->date('fecha_crp')->nullable();
            $table->decimal('valor_crp', 15, 2)->nullable();
            $table->decimal('recursos_sgp', 15, 2)->nullable();
            $table->decimal('recursos_sgr', 15, 2)->nullable();
            $table->decimal('recursos_pgn', 15, 2)->nullable();
            $table->decimal('otros_recursos', 15, 2)->nullable();
            $table->string('producto_mga', 100)->nullable();
            $table->string('producto_cpc', 100)->nullable();
            $table->string('fuente_recursos_rp', 100)->nullable();
            $table->string('fuente_recurso', 100)->nullable();
            $table->string('fuente_financiacion', 100)->nullable();
            $table->string('codigo_rubro', 60)->nullable();
            $table->string('nombre_rubro', 150)->nullable();
            $table->decimal('valor_rubro', 15, 2)->nullable();

            // --- ANTICIPO ---
            $table->boolean('tiene_anticipo')->default(false);
            $table->string('tipo_anticipo', 60)->nullable();
            $table->decimal('porcentaje_anticipo', 5, 2)->nullable();
            $table->decimal('valor_anticipo', 15, 2)->nullable();
            $table->date('fecha_anticipo')->nullable();

            // --- PAGOS PARCIALES (JSON) ---
            $table->json('pagos_parciales')->nullable();

            // --- LIQUIDACIÓN ---
            $table->date('fecha_acta_recibo_final')->nullable();
            $table->date('fecha_acta_liquidacion')->nullable();
            $table->decimal('valor_acta_liquidacion', 15, 2)->nullable();
            $table->date('fecha_reversion_saldo')->nullable();
            $table->decimal('valor_reversion', 15, 2)->nullable();

            // --- EXTRA ---
            $table->text('link_secop')->nullable();
            $table->string('recursos_reactivacion', 60)->nullable();
            $table->string('funciones', 150)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índice compuesto Key 2
            $table->unique(['numero_constancia_secop', 'vigencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
