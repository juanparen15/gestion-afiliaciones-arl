<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Afiliacion extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'afiliaciones';

    protected $fillable = [
        'nombre_contratista',
        'tipo_documento',
        'numero_documento',
        'email_contratista',
        'telefono_contratista',
        'fecha_nacimiento',
        'barrio',
        'direccion_residencia',
        'eps',
        'afp',
        'numero_contrato',
        'objeto_contractual',
        'valor_contrato',
        'honorarios_mensual',
        'ibc',
        'fecha_inicio',
        'fecha_fin',
        'meses_contrato',
        'dias_contrato',
        'supervisor_contrato',
        'tiene_adicion',
        'descripcion_adicion',
        'valor_adicion',
        'fecha_adicion',
        'tiene_prorroga',
        'descripcion_prorroga',
        'meses_prorroga',
        'dias_prorroga',
        'nueva_fecha_fin_prorroga',
        'tiene_terminacion_anticipada',
        'fecha_terminacion_anticipada',
        'motivo_terminacion_anticipada',
        'nombre_arl',
        'observaciones_arl',
        'tipo_riesgo',
        'numero_afiliacion_arl',
        'fecha_afiliacion_arl',
        'fecha_terminacion_afiliacion',
        'pdf_arl',
        'contrato_pdf_o_word',
        'dependencia_id',
        'area_id',
        'created_by',
        'validated_by',
        'estado',
        'observaciones',
        'motivo_rechazo',
        'fecha_validacion',
    ];

    protected $casts = [
        'valor_contrato' => 'decimal:2',
        'honorarios_mensual' => 'decimal:2',
        'ibc' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_nacimiento' => 'date',
        'fecha_afiliacion_arl' => 'date',
        'fecha_terminacion_afiliacion' => 'date',
        'fecha_validacion' => 'datetime',
        'meses_contrato' => 'integer',
        'dias_contrato' => 'integer',
        'tiene_adicion' => 'boolean',
        'valor_adicion' => 'decimal:2',
        'fecha_adicion' => 'date',
        'tiene_prorroga' => 'boolean',
        'meses_prorroga' => 'integer',
        'dias_prorroga' => 'integer',
        'nueva_fecha_fin_prorroga' => 'date',
        'tiene_terminacion_anticipada' => 'boolean',
        'fecha_terminacion_anticipada' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Validar que los honorarios mensuales no sean menores al salario mínimo legal
        static::saving(function ($afiliacion) {
            $salarioMinimo = config('constants.salario_minimo_legal', 1423500);
            if ($afiliacion->honorarios_mensual && $afiliacion->honorarios_mensual < $salarioMinimo) {
                throw new \Exception("Los honorarios mensuales no pueden ser menores al salario mínimo legal vigente en Colombia ($" . number_format($salarioMinimo, 0, ',', '.') . ")");
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ArchivoAfiliacion::class);
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeValidado($query)
    {
        return $query->where('estado', 'validado');
    }

    public function scopeRechazado($query)
    {
        return $query->where('estado', 'rechazado');
    }

    public function scopeVigente($query)
    {
        return $query->where('fecha_fin', '>=', now());
    }
}
