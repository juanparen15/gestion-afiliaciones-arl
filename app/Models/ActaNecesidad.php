<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActaNecesidad extends Model
{
    use SoftDeletes;

    protected $table = 'actas_necesidad';

    protected $fillable = [
        'consecutivo',
        'email_solicitante',
        'dependencia_id',
        'area_id',
        'dependencia_nombre',
        'area_nombre',
        'nombre_solicitante',
        'objeto_contrato',
        'tipo_contrato',
        'duracion',
        'modalidad_seleccion',
        'tipo_solicitud',
        'numero_contrato_convenio',
        'presupuesto_oficial',
        'codigo_bpim_bpin',
        'codigo_paa',
        'observaciones',
        'nombre_completo',
        'estado',
        'motivo_rechazo',
        'fecha_solicitud',
        'fecha_generado',
        'fecha_aprobado',
        'pdf_path',
        'created_by',
        'aprobado_por',
    ];

    protected $casts = [
        'consecutivo'        => 'integer',
        'presupuesto_oficial'=> 'decimal:2',
        'fecha_solicitud'    => 'datetime',
        'fecha_generado'     => 'datetime',
        'fecha_aprobado'     => 'datetime',
    ];

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

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    /** Siguiente consecutivo disponible. */
    public static function siguienteConsecutivo(): int
    {
        return (int) static::max('consecutivo') + 1;
    }

    /** Nombre de dependencia (relación o texto denormalizado). */
    public function getDependenciaTextoAttribute(): string
    {
        return $this->dependencia?->nombre ?: (string) $this->dependencia_nombre;
    }

    public function getAreaTextoAttribute(): string
    {
        return $this->area?->nombre ?: (string) $this->area_nombre;
    }

    public function scopePendiente($q)
    {
        return $q->where('estado', 'pendiente');
    }
}
