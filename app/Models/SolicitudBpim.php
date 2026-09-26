<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SolicitudBpim extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'solicitudes_bpim';

    protected $fillable = [
        'codigo',
        'consecutivo',
        'dependencia',
        'cartera',
        'autorizacion_uso_datos',
        'numero_item_presupuesto',
        'nombre_solicitante',
        'correo_solicitante',
        'correo_notificacion',
        'proyecto_id',
        'nombre_proyecto',
        'codigo_bpim',
        'codigo_bpin',
        'objeto',
        'cdp',
        'valor_cdp',
        'valor_ep',
        'objeto_gasto',
        'fuente_recursos',
        'otra_fuente_recurso',
        'mga',
        'cpc',
        'sector',
        'programa',
        'subprograma',
        'pdn_sector',
        'pdn_programa',
        'pdn_subprograma',
        'pdm_sector',
        'pdm_programa',
        'pdm_producto',
        'tiempo_contractual',
        'codigo_acta_necesidad',
        'nombre_elabora',
        'aprobar',
        'aprobado',
        'aprobado_enviado',
        'rechazado',
        'rechazo_enviado',
        'check',
        'motivo_rechazo',
        'url_documento_word',
        'url_documento_pdf',
        'ruta_carpeta',
        'fecha_generacion',
        'fecha_aprobacion',
        'fecha_rechazo',
        'fecha_envio',
        'fecha_modificacion_word',
        'fecha_generacion_pdf',
        'usuario_ultima_edicion',
        'usuario_elaboro',
        'usuario_aprobo',
        'fecha_aprobacion_secretario',
    ];

    protected $casts = [
        'aprobar' => 'boolean',
        'aprobado' => 'boolean',
        'aprobado_enviado' => 'boolean',
        'rechazado' => 'boolean',
        'rechazo_enviado' => 'boolean',
        'check' => 'boolean',
        'autorizacion_uso_datos' => 'boolean',
        'valor_cdp' => 'decimal:2',
        'valor_ep' => 'decimal:2',
        'fecha_generacion' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'fecha_rechazo' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_modificacion_word' => 'datetime',
        'fecha_generacion_pdf' => 'datetime',
    ];

    /**
     * Relación: Una solicitud tiene muchos items de presupuesto
     */
    public function presupuestoItems(): HasMany
    {
        return $this->hasMany(PresupuestoItem::class, 'solicitud_bpim_id')
            ->orderBy('orden');
    }

    /**
     * Relación: Una solicitud puede estar vinculada a un proyecto
     */
    public function proyecto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Proyecto::class, 'proyecto_id');
    }

    // Scopes
    public function scopePendientes($query)
    {
        return $query->where('aprobar', true)
            ->where('aprobado', false)
            ->where('rechazado', false);
    }

    public function scopeAprobados($query)
    {
        return $query->where('aprobado', true);
    }

    public function scopeRechazados($query)
    {
        return $query->where('rechazado', true);
    }

    public function scopeNoEnviados($query)
    {
        return $query->where('aprobado', true)
            ->where('aprobado_enviado', false);
    }

    // Accesorios
    public function getEstadoAttribute(): string
    {
        if ($this->rechazado) {
            return 'Rechazado';
        }
        if ($this->aprobado && $this->aprobado_enviado) {
            return 'Aprobado y Enviado';
        }
        if ($this->aprobado) {
            return 'Aprobado';
        }
        if ($this->aprobar) {
            return 'Pendiente de Aprobación';
        }
        return 'Borrador';
    }

    public function getEstadoColorAttribute(): string
    {
        return match ($this->estado) {
            'Aprobado y Enviado' => 'success',
            'Aprobado' => 'info',
            'Rechazado' => 'danger',
            'Pendiente de Aprobación' => 'warning',
            default => 'gray',
        };
    }

    public function getValorTotalPresupuestoAttribute(): float
    {
        return $this->presupuestoItems->sum('valor_total');
    }

    // Métodos de negocio
    public function generarCodigo(): string
    {
        $year = now()->year;
        $ultimoCodigo = static::whereYear('created_at', $year)
            ->whereNotNull('codigo')
            ->orderBy('codigo', 'desc')
            ->first();

        if ($ultimoCodigo) {
            $numero = (int) substr($ultimoCodigo->codigo, -3) + 1;
        } else {
            $numero = 1;
        }

        $this->codigo = 'BPIM-' . $year . '-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
        $this->save();

        return $this->codigo;
    }

    public function esCorreoInstitucional(): bool
    {
        return str_contains($this->correo_solicitante, '@puertoboyaca-boyaca.gov.co');
    }

    public function aprobarSolicitud(): void
    {
        $this->update([
            'aprobado' => true,
            'fecha_aprobacion' => now(),
            'fecha_aprobacion_secretario' => now(),
            'usuario_aprobo' => auth()->user()?->name ?? 'Secretario',
            'rechazado' => false,
            'motivo_rechazo' => null,
        ]);
    }

    public function rechazarSolicitud(string $motivo): void
    {
        $this->update([
            'rechazado' => true,
            'fecha_rechazo' => now(),
            'motivo_rechazo' => $motivo,
            'aprobado' => false,
            'usuario_aprobo' => null,
            'fecha_aprobacion_secretario' => null,
        ]);
    }

    public function marcarComoEnviado(): void
    {
        $this->update([
            'aprobado_enviado' => true,
            'fecha_envio' => now(),
        ]);
    }

    public function eliminarArchivos(): void
    {
        if ($this->url_documento_word) {
            Storage::delete($this->url_documento_word);
        }
        if ($this->url_documento_pdf) {
            Storage::delete($this->url_documento_pdf);
        }
    }

    // Eventos del modelo
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($solicitud) {
            // Convertir campos a mayúsculas
            $solicitud->nombre_solicitante = strtoupper($solicitud->nombre_solicitante);
            $solicitud->dependencia = strtoupper($solicitud->dependencia);
            $solicitud->nombre_proyecto = strtoupper($solicitud->nombre_proyecto);
            if ($solicitud->objeto) {
                $solicitud->objeto = strtoupper($solicitud->objeto);
            }
        });

        static::deleting(function ($solicitud) {
            // Eliminar archivos al borrar la solicitud
            $solicitud->eliminarArchivos();

            // Eliminar items de presupuesto
            $solicitud->presupuestoItems()->delete();
        });
    }

    public function verificacion(): HasOne
    {
        return $this->hasOne(DocumentoVerificacion::class, 'solicitud_bpim_id');
    }

}
