<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresupuestoItem extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_items';

    protected $fillable = [
        'solicitud_bpim_id',
        'item_catalogo_id',
        'descripcion',
        'valor_unitario',
        'unidad',
        'cantidad',
        'valor_total',
        'orden',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'cantidad' => 'integer',
        'orden' => 'integer',
    ];

    /**
     * Relación: Un item de presupuesto pertenece a una solicitud
     */
    public function solicitudBpim(): BelongsTo
    {
        return $this->belongsTo(SolicitudBpim::class, 'solicitud_bpim_id');
    }

    /**
     * Relación: Un item de presupuesto puede estar vinculado a un ítem del catálogo
     */
    public function itemCatalogo(): BelongsTo
    {
        return $this->belongsTo(\App\Models\ItemCatalogo::class, 'item_catalogo_id');
    }

    /**
     * Calcular automáticamente el valor total
     */
    public function calcularValorTotal(): float
    {
        return ($this->valor_unitario ?? 0) * ($this->cantidad ?? 0);
    }

    /**
     * Eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            // Calcular automáticamente el valor total si no está definido
            if (!$item->valor_total && $item->valor_unitario && $item->cantidad) {
                $item->valor_total = $item->calcularValorTotal();
            }
        });
    }
}