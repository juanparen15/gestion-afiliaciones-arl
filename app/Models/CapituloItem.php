<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CapituloItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'capitulo_items';

    protected $fillable = [
        'proyecto_capitulo_id',
        'item_catalogo_id',
        'etapa',
        'tipo',
        'item',
        'anio',
        'descripcion',
        'perfil',
        'valor_unitario',
        'unidad',
        'cantidad',
        'valor_total',
        'cantidad_ejecutada',
        'valor_ejecutado',
        'valor_restante',
        'orden',
    ];

    protected $casts = [
        'valor_unitario' => 'decimal:2',
        'cantidad' => 'decimal:2',
        'valor_total' => 'decimal:2',
        'cantidad_ejecutada' => 'decimal:2',
        'valor_ejecutado' => 'decimal:2',
        'valor_restante' => 'decimal:2',
        'anio' => 'integer',
    ];

    /**
     * Relación: Un ítem pertenece a un capítulo de proyecto
     */
    public function capitulo(): BelongsTo
    {
        return $this->belongsTo(ProyectoCapitulo::class, 'proyecto_capitulo_id');
    }

    /**
     * Relación: Un ítem puede estar referenciado desde el catálogo
     */
    public function itemCatalogo(): BelongsTo
    {
        return $this->belongsTo(ItemCatalogo::class, 'item_catalogo_id');
    }

    /**
     * Calcula el valor total del ítem
     */
    public function calcularValorTotal(): void
    {
        $this->valor_total = $this->valor_unitario * $this->cantidad;
        $this->valor_restante = $this->valor_total - $this->valor_ejecutado;
    }

    /**
     * Recalcula los valores del ítem
     */
    public function recalcularValores(): void
    {
        $this->valor_total = $this->valor_unitario * $this->cantidad;
        $this->valor_restante = $this->valor_total - $this->valor_ejecutado;
        $this->save();
    }

    /**
     * Registra una ejecución del ítem
     */
    public function registrarEjecucion(float $cantidad, float $valor): void
    {
        $this->cantidad_ejecutada += $cantidad;
        $this->valor_ejecutado += $valor;
        $this->valor_restante = $this->valor_total - $this->valor_ejecutado;
        $this->save();

        // Actualizar el catálogo si está vinculado
        if ($this->itemCatalogo) {
            $this->itemCatalogo->recalcularValores();
        }

        // Actualizar el capítulo
        $this->capitulo->recalcularValores();

        // Actualizar el proyecto
        $this->capitulo->proyecto->recalcularValores();
    }

    /**
     * Verifica si se puede ejecutar una cantidad adicional
     */
    public function puedeEjecutar(float $cantidad): bool
    {
        $valorNecesario = $cantidad * $this->valor_unitario;
        return $this->valor_restante >= $valorNecesario;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            $item->item = strtoupper($item->item);
            $item->descripcion = strtoupper($item->descripcion);
            $item->calcularValorTotal();
        });

        static::updating(function ($item) {
            $item->item = strtoupper($item->item);
            $item->descripcion = strtoupper($item->descripcion);
            $item->calcularValorTotal();
        });

        // Eventos deshabilitados para mejorar performance
        // Los valores se recalcularán después de guardar el proyecto completo
    }
}
