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

    // El id de cada catálogo UNSPSC es su código jerárquico (segmento 2, familia 4, clase 6, producto 8 dígitos).
    public function getSegmentoNombreAttribute(): ?string
    {
        $s = $this->claseEfectiva()?->familia?->segmento;

        return $s ? "{$s->id} — {$s->detsegmento}" : null;
    }

    public function getFamiliaNombreAttribute(): ?string
    {
        $f = $this->claseEfectiva()?->familia;

        return $f ? "{$f->id} — {$f->detfamilia}" : null;
    }

    public function getClaseNombreAttribute(): ?string
    {
        $c = $this->claseEfectiva();

        return $c ? "{$c->id} — {$c->detclase}" : null;
    }

    public function getProductoNombreAttribute(): ?string
    {
        $p = $this->producto;

        return $p ? "{$p->id} — {$p->detproducto}" : null;
    }
}
