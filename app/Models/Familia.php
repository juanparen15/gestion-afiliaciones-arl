<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Familia extends Model
{
    protected $guarded = [];

    public function segmento(): BelongsTo
    {
        return $this->belongsTo(Segmento::class);
    }

    public function clases(): HasMany
    {
        return $this->hasMany(Clase::class);
    }
}
