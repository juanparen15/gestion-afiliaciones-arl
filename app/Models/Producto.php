<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    protected $guarded = [];

    public function clase(): BelongsTo
    {
        return $this->belongsTo(Clase::class);
    }
}
