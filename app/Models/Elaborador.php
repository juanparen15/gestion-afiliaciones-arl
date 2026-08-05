<?php

namespace App\Models;

use App\Models\Concerns\ProtegeBorrado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Elaborador extends Model
{
    use ProtegeBorrado;

    protected $table = 'elaboradores';

    protected $fillable = [
        'nombre',
        'cargo',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    public function procesos(): HasMany
    {
        return $this->hasMany(ProcesoSeleccion::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /** Busca (o crea) un elaborador por nombre. Devuelve null si el nombre viene vacío. */
    public static function buscarOCrear(?string $nombre): ?self
    {
        $nombre = trim((string) $nombre);
        if ($nombre === '') {
            return null;
        }

        return static::firstOrCreate(['nombre' => mb_strtoupper($nombre)]);
    }
}
