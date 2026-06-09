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
}
