<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proyecto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proyectos';

    protected $fillable = [
        'nombre_proyecto',
        'codigo_bpim',
        'codigo_bpin',
        'valor_total',
        'valor_total_con_ajuste',
        'valor_ejecutado',
        'valor_restante',
        'porcentaje_ejecucion',
        'descripcion',
        'estado',
    ];

    protected $attributes = [
        'valor_total' => 0,
        'valor_total_con_ajuste' => 0,
        'valor_ejecutado' => 0,
        'valor_restante' => 0,
        'porcentaje_ejecucion' => 0,
        'estado' => 'activo',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_total_con_ajuste' => 'decimal:2',
        'valor_ejecutado' => 'decimal:2',
        'valor_restante' => 'decimal:2',
        'porcentaje_ejecucion' => 'decimal:2',
    ];

    /**
     * Relación: Un proyecto tiene muchos capítulos
     */
    public function capitulos(): HasMany
    {
        return $this->hasMany(ProyectoCapitulo::class, 'proyecto_id')
            ->orderBy('orden')
            ->orderBy('numero_capitulo');
    }

    /**
     * Relación: Un proyecto tiene muchos items en el catálogo
     */
    public function itemsCatalogo(): HasMany
    {
        return $this->hasMany(\App\Models\ItemCatalogo::class, 'proyecto_origen_id');
    }

    /**
     * Calcula y actualiza los valores totales del proyecto
     */
    public function recalcularValores(): void
    {
        $this->capitulos->each->recalcularValores();

        $valorTotalCapitulos = $this->capitulos->sum('valor_total');
        $valorEjecutadoCapitulos = $this->capitulos->sum('valor_ejecutado');

        $this->valor_total = $valorTotalCapitulos;
        $this->valor_ejecutado = $valorEjecutadoCapitulos;
        $this->valor_restante = $this->valor_total - $this->valor_ejecutado;

        if ($this->valor_total > 0) {
            $this->porcentaje_ejecucion = ($this->valor_ejecutado / $this->valor_total) * 100;
        } else {
            $this->porcentaje_ejecucion = 0;
        }

        $this->save();
    }

    /**
     * Obtiene el estado con formato
     */
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'activo' => 'success',
            'finalizado' => 'info',
            'cancelado' => 'danger',
            default => 'gray',
        };
    }

    /**
     * Scope para proyectos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }

    /**
     * Scope para proyectos finalizados
     */
    public function scopeFinalizados($query)
    {
        return $query->where('estado', 'finalizado');
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($proyecto) {
            $proyecto->nombre_proyecto = strtoupper($proyecto->nombre_proyecto);
        });

        static::updating(function ($proyecto) {
            $proyecto->nombre_proyecto = strtoupper($proyecto->nombre_proyecto);
        });

        static::deleting(function ($proyecto) {
            // Eliminar todos los capítulos (y sus ítems en cascada)
            $proyecto->capitulos()->delete();
        });
    }
}
