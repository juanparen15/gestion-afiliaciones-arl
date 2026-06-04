<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Segmento extends Model
{
    protected $guarded = [];

    public function familias(): HasMany
    {
        return $this->hasMany(Familia::class);
    }
}
