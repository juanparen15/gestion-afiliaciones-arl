<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCatalogo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'items_catalogo';

    protected $fillable = [
        'proyecto_origen_id',
        'codigo',
        'nombre',
        'descripcion',
        'valor_unitario',
        'unidad',
        'cantidad',
        'tipo',
        'etapa',
        'perfil',
        'activo',
        'valor_ejecutado',
        'valor_restante',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'cantidad' => 'integer',
        'valor_ejecutado' => 'decimal:2',
        'valor_restante' => 'decimal:2',
        'activo' => 'boolean',
    ];

    /**
     * Relación: Un ítem del catálogo puede estar en muchos capítulos
     */
    public function usos(): HasMany
    {
        return $this->hasMany(CapituloItem::class, 'item_catalogo_id');
    }

    /**
     * Relación: Un ítem pertenece a un proyecto origen
     */
    public function proyectoOrigen()
    {
        return $this->belongsTo(\App\Models\Proyecto::class, 'proyecto_origen_id');
    }

    /**
     * Recalcula el valor ejecutado del ítem en el catálogo
     */
    public function recalcularValores(): void
    {
        $this->valor_ejecutado = $this->usos()->sum('valor_ejecutado');
        $this->valor_restante = 0 - $this->valor_ejecutado;
        $this->save();
    }

    /**
     * Scope para ítems activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtiene el nombre completo del ítem
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->codigo} - {$this->nombre}";
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->nombre = strtoupper($item->nombre);
            $item->descripcion = strtoupper($item->descripcion);
            $item->valor_restante ??= ($item->valor_unitario ?? 0) * ($item->cantidad ?? 0);
        });

        static::updating(function ($item) {
            $item->nombre = strtoupper($item->nombre);
            $item->descripcion = strtoupper($item->descripcion);
        });
    }
}
