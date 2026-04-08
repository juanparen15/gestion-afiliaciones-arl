<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Contrato extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'contratos';

    protected $fillable = [
        // Identificación
        'numero_contrato',
        'vigencia',
        'id_contrato_secop',
        'numero_constancia_secop',
        'estado',
        'tipo',
        'tipo_contrato',
        'clase',
        'modalidad',
        'modalidad_seleccion',
        'misional_apoyo',
        'profesional_encargado',
        'fecha_ultima_modificacion',
        // Entidad
        'nit_entidad',
        'entidad',
        'unidad_ejecucion',
        // Interventoría
        'cto_interventoria',
        'nombre_interventor',
        'direccion_interventor',
        'documento_interventor',
        // Objeto y fechas
        'objeto',
        'fecha_contrato',
        'fecha_aprobacion',
        'fecha_inicio',
        'fecha_terminacion',
        'plazo_anos',
        'plazo_meses',
        'plazo_dias',
        // Adición 1
        'valor_adicional_1',
        'fecha_adicional_1',
        'plazo_anos_adicional_1',
        'plazo_meses_adicional_1',
        'plazo_dias_adicional_1',
        // Adición 2
        'valor_adicional_2',
        'fecha_adicional_2',
        'plazo_anos_adicional_2',
        'plazo_meses_adicional_2',
        'plazo_dias_adicional_2',
        // Adición 3
        'valor_adicional_3',
        'fecha_adicional_3',
        'plazo_anos_adicional_3',
        'plazo_meses_adicional_3',
        'plazo_dias_adicional_3',
        // Prórroga 1
        'fecha_prorroga_1',
        'plazo_anos_prorroga_1',
        'plazo_meses_prorroga_1',
        'plazo_dias_prorroga_1',
        // Prórroga 2
        'fecha_prorroga_2',
        'plazo_anos_prorroga_2',
        'plazo_meses_prorroga_2',
        'plazo_dias_prorroga_2',
        // Prórroga 3
        'fecha_prorroga_3',
        'plazo_anos_prorroga_3',
        'plazo_meses_prorroga_3',
        'plazo_dias_prorroga_3',
        // Financiero
        'valor_contrato',
        // CDP
        'solicitud_cdp',
        'fecha_solicitud_cdp',
        'numero_cdp',
        'fecha_cdp',
        'valor_cdp',
        // BPIN
        'solicitud_bpin',
        'codigo_bpim',
        'codigo_bpin',
        'nombre_proyecto',
        'sector',
        'programa',
        'subprograma',
        'dependencia_proyecto',
        'meta_plan_desarrollo',
        // Modalidad / tipo
        'codigo_unspsc',
        'descripcion_unspsc',
        'segmento_servicio',
        'acta_ampliacion_plazo',
        'dias_ampliacion',
        'total_dias_ampliacion',
        'numero_suspensiones',
        'dias_suspension',
        'fecha_acta_reinicio',
        // Bien inmueble
        'direccion_bien_inmueble',
        'matricula',
        'codigo_catastral',
        // Persona natural
        'nombre_persona_natural',
        'genero',
        'cedula',
        'lugar_expedicion_cedula',
        'fecha_expedicion_cedula',
        'lugar_nacimiento',
        'departamento_nacimiento',
        'fecha_nacimiento',
        'titulo_bachiller',
        'ano_bachiller',
        'titulo_profesional',
        'universidad',
        'perfil',
        'ano_grado_profesional',
        'especializaciones',
        'universidad_posgrado',
        'ano_grado_posgrado',
        'correo_contratista',
        // Persona jurídica
        'nombre_persona_juridica',
        'nit_contratista',
        'dv',
        'direccion_contratista',
        'ciudad_contratista',
        'telefono_contratista',
        'entidad_bancaria',
        'tipo_cuenta_bancaria',
        'numero_cuenta_bancaria',
        // Consorcio / UT
        'integrante_1_consorcio',
        'participacion_1',
        'tipo_doc_integrante_1',
        'doc_integrante_1',
        'direccion_integrante_1',
        'integrante_2_consorcio',
        'participacion_2',
        'tipo_doc_integrante_2',
        'doc_integrante_2',
        'direccion_integrante_2',
        'integrante_3_consorcio',
        'participacion_3',
        'tipo_doc_integrante_3',
        'doc_integrante_3',
        'direccion_integrante_3',
        // Supervisión
        'dependencia_contrato',
        'asignado_supervision',
        'tipo_supervision',
        'identificacion_supervisor',
        'titulo_supervisor',
        'nombre_supervisor',
        'cargo_supervisor',
        'tipo_vinculacion_supervisor',
        'oficina_supervisor',
        'fecha_designacion_supervision',
        // Pólizas
        'acta_aprobacion_poliza',
        'fecha_aprobacion_poliza',
        'compannia_aseguradora',
        'nit_aseguradora',
        'poliza_cumplimiento',
        'anexo_cumplimiento',
        'fecha_expedicion_poliza_cumplimiento',
        'vigencia_cumplimiento',
        'vigencia_pago_anticipado',
        'vigencia_pago_salarios',
        'vigencia_calidad_servicio',
        'poliza_responsabilidad',
        'anexo_responsabilidad',
        'fecha_expedicion_poliza_responsabilidad',
        'vigencia_responsabilidad',
        // CRP / Recursos
        'numero_crp',
        'fecha_crp',
        'valor_crp',
        'recursos_sgp',
        'recursos_sgr',
        'recursos_pgn',
        'otros_recursos',
        'producto_mga',
        'producto_cpc',
        'fuente_recursos_rp',
        'fuente_recurso',
        'fuente_financiacion',
        'codigo_rubro',
        'nombre_rubro',
        'valor_rubro',
        // Anticipo
        'tiene_anticipo',
        'tipo_anticipo',
        'porcentaje_anticipo',
        'valor_anticipo',
        'fecha_anticipo',
        // Pagos parciales
        'pagos_parciales',
        // Liquidación
        'fecha_acta_recibo_final',
        'fecha_acta_liquidacion',
        'valor_acta_liquidacion',
        'fecha_reversion_saldo',
        'valor_reversion',
        // Relación
        'dependencia_id',
        // Extra
        'link_secop',
        'recursos_reactivacion',
        'funciones',
        'observaciones',
    ];

    protected $casts = [
        // Fechas
        'fecha_ultima_modificacion'              => 'date',
        'fecha_contrato'                         => 'date',
        'fecha_aprobacion'                       => 'date',
        'fecha_inicio'                           => 'date',
        'fecha_terminacion'                      => 'date',
        'fecha_adicional_1'                      => 'date',
        'fecha_adicional_2'                      => 'date',
        'fecha_adicional_3'                      => 'date',
        'fecha_prorroga_1'                       => 'date',
        'fecha_prorroga_2'                       => 'date',
        'fecha_prorroga_3'                       => 'date',
        'fecha_solicitud_cdp'                    => 'date',
        'fecha_cdp'                              => 'date',
        'fecha_acta_reinicio'                    => 'date',
        'fecha_expedicion_cedula'                => 'date',
        'fecha_nacimiento'                       => 'date',
        'fecha_designacion_supervision'          => 'date',
        'fecha_aprobacion_poliza'                => 'date',
        'fecha_expedicion_poliza_cumplimiento'   => 'date',
        'fecha_expedicion_poliza_responsabilidad'=> 'date',
        'fecha_crp'                              => 'date',
        'fecha_anticipo'                         => 'date',
        'fecha_acta_recibo_final'                => 'date',
        'fecha_acta_liquidacion'                 => 'date',
        'fecha_reversion_saldo'                  => 'date',
        // Decimales
        'valor_contrato'         => 'decimal:2',
        'valor_adicional_1'      => 'decimal:2',
        'valor_adicional_2'      => 'decimal:2',
        'valor_adicional_3'      => 'decimal:2',
        'valor_cdp'              => 'decimal:2',
        'valor_crp'              => 'decimal:2',
        'recursos_sgp'           => 'decimal:2',
        'recursos_sgr'           => 'decimal:2',
        'recursos_pgn'           => 'decimal:2',
        'otros_recursos'         => 'decimal:2',
        'valor_rubro'            => 'decimal:2',
        'porcentaje_anticipo'    => 'decimal:2',
        'valor_anticipo'         => 'decimal:2',
        'valor_acta_liquidacion' => 'decimal:2',
        'valor_reversion'        => 'decimal:2',
        // Booleano
        'tiene_anticipo' => 'boolean',
        // JSON
        'pagos_parciales' => 'array',
        // Enteros
        'numero_contrato'           => 'integer',
        'plazo_anos'                => 'integer',
        'plazo_meses'               => 'integer',
        'plazo_dias'                => 'integer',
        'plazo_anos_adicional_1'    => 'integer',
        'plazo_meses_adicional_1'   => 'integer',
        'plazo_dias_adicional_1'    => 'integer',
        'plazo_anos_adicional_2'    => 'integer',
        'plazo_meses_adicional_2'   => 'integer',
        'plazo_dias_adicional_2'    => 'integer',
        'plazo_anos_adicional_3'    => 'integer',
        'plazo_meses_adicional_3'   => 'integer',
        'plazo_dias_adicional_3'    => 'integer',
        'plazo_anos_prorroga_1'     => 'integer',
        'plazo_meses_prorroga_1'    => 'integer',
        'plazo_dias_prorroga_1'     => 'integer',
        'plazo_anos_prorroga_2'     => 'integer',
        'plazo_meses_prorroga_2'    => 'integer',
        'plazo_dias_prorroga_2'     => 'integer',
        'plazo_anos_prorroga_3'     => 'integer',
        'plazo_meses_prorroga_3'    => 'integer',
        'plazo_dias_prorroga_3'     => 'integer',
        'dias_ampliacion'           => 'integer',
        'total_dias_ampliacion'     => 'integer',
        'numero_suspensiones'       => 'integer',
        'dias_suspension'           => 'integer',
        'ano_bachiller'             => 'integer',
        'ano_grado_profesional'     => 'integer',
        'ano_grado_posgrado'        => 'integer',
        'dv'                        => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    // Relaciones
    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    // Scopes
    public function scopeVigentes($query)
    {
        return $query->where('fecha_terminacion', '>=', now()->toDateString());
    }

    public function scopePorVencer($query, int $dias = 30)
    {
        return $query->whereBetween('fecha_terminacion', [
            now()->toDateString(),
            now()->addDays($dias)->toDateString(),
        ]);
    }

    public function scopeVigencia($query, string $year)
    {
        return $query->where('vigencia', $year);
    }

    // Helpers
    public function getNombreContratista(): ?string
    {
        return $this->nombre_persona_natural ?? $this->nombre_persona_juridica;
    }

    /**
     * Fecha de cierre real del contrato, incluyendo plazos de adiciones.
     * Suma años, meses y días de cada adición a fecha_terminacion.
     */
    public function fechaEfectivaCierre(): ?\Carbon\Carbon
    {
        if (!$this->fecha_terminacion) return null;

        $fecha = $this->fecha_terminacion->copy();

        foreach ([1, 2, 3] as $n) {
            if ($this->{"fecha_adicional_{$n}"}) {
                $fecha->addYears((int) ($this->{"plazo_anos_adicional_{$n}"} ?? 0));
                $fecha->addMonths((int) ($this->{"plazo_meses_adicional_{$n}"} ?? 0));
                $fecha->addDays((int) ($this->{"plazo_dias_adicional_{$n}"} ?? 0));
            }
            if ($this->{"fecha_prorroga_{$n}"}) {
                $fecha->addYears((int) ($this->{"plazo_anos_prorroga_{$n}"} ?? 0));
                $fecha->addMonths((int) ($this->{"plazo_meses_prorroga_{$n}"} ?? 0));
                $fecha->addDays((int) ($this->{"plazo_dias_prorroga_{$n}"} ?? 0));
            }
        }

        return $fecha;
    }

    /**
     * Indica si el contrato tiene al menos una adición registrada.
     */
    public function tieneAdiciones(): bool
    {
        return $this->fecha_adicional_1 !== null
            || $this->fecha_adicional_2 !== null
            || $this->fecha_adicional_3 !== null;
    }
}
