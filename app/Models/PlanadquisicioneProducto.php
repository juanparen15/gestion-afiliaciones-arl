<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cada registro es una línea de clasificación UNSPSC de un plan:
 * una clase y, opcionalmente, un producto de esa clase.
 */
class PlanadquisicioneProducto extends Model
{
    protected $table = 'planadquisicione_producto';

    protected $guarded = [];

    public function planadquisicione(): BelongsTo
    {
        return $this->belongsTo(Planadquisicione::class);
    }

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    /** Clase efectiva: la propia, o la del producto si la fila solo trae producto_id. */
    public function claseEfectiva(): ?Clase
    {
        if ($this->clase_id) {
            return $this->clase;
        }

        return $this->producto?->clase;
    }

    public function getSegmentoNombreAttribute(): ?string
    {
        return $this->claseEfectiva()?->familia?->segmento?->detsegmento;
    }

    public function getFamiliaNombreAttribute(): ?string
    {
        return $this->claseEfectiva()?->familia?->detfamilia;
    }

    public function getClaseNombreAttribute(): ?string
    {
        return $this->claseEfectiva()?->detclase;
    }

    public function getProductoNombreAttribute(): ?string
    {
        return $this->producto?->detproducto;
    }
}
