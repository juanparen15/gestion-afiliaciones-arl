<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ActaNecesidad extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'actas_necesidad';

    protected $fillable = [
        'consecutivo',
        'codigo_verificacion',
        'email_solicitante',
        'dependencia_id',
        'area_id',
        'dependencia_nombre',
        'area_nombre',
        'nombre_solicitante',
        'nombre_secretario_supervisor',
        'objeto_contrato',
        'tipo_contrato',
        'duracion',
        'duracion_valor',
        'duracion_unidad',
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
        'motivo_anulacion',
        'fecha_solicitud',
        'fecha_generado',
        'fecha_aprobado',
        'fecha_anulacion',
        'pdf_path',
        'created_by',
        'aprobado_por',
        'anulado_por',
    ];

    protected $casts = [
        'consecutivo'        => 'integer',
        'presupuesto_oficial'=> 'decimal:2',
        'fecha_solicitud'    => 'datetime',
        'fecha_generado'     => 'datetime',
        'fecha_aprobado'     => 'datetime',
        'fecha_anulacion'    => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['estado', 'consecutivo', 'motivo_rechazo', 'motivo_anulacion', 'aprobado_por', 'anulado_por'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        // Componer la duración textual (ej. "3 MESES") a partir de valor + unidad
        static::saving(function (ActaNecesidad $acta) {
            if ($acta->duracion_valor && $acta->duracion_unidad) {
                $acta->duracion = trim($acta->duracion_valor . ' ' . $acta->duracion_unidad);
            }
        });
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

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function anulador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    /** Siguiente consecutivo disponible. */
    public static function siguienteConsecutivo(): int
    {
        return (int) static::max('consecutivo') + 1;
    }

    /** Genera (una vez) un código de verificación único para el QR. */
    public function asegurarCodigoVerificacion(): string
    {
        if (! $this->codigo_verificacion) {
            do {
                $codigo = strtoupper(bin2hex(random_bytes(6))); // 12 hex
            } while (static::where('codigo_verificacion', $codigo)->exists());
            $this->codigo_verificacion = $codigo;
        }
        return $this->codigo_verificacion;
    }

    /** URL pública de verificación de autenticidad. */
    public function urlVerificacion(): ?string
    {
        return $this->codigo_verificacion
            ? url('/actas/verificar/' . $this->codigo_verificacion)
            : null;
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
