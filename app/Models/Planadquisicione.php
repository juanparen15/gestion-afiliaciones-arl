<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planadquisicione extends Model
{
    protected $guarded = [];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function intervalo(): BelongsTo
    {
        return $this->belongsTo(Intervalo::class);
    }

    public function vigenfutura(): BelongsTo
    {
        return $this->belongsTo(Vigenfutura::class);
    }

    public function tipozona(): BelongsTo
    {
        return $this->belongsTo(Tipozona::class);
    }

    public function estadovigencia(): BelongsTo
    {
        return $this->belongsTo(Estadovigencia::class);
    }

    public function modalidade(): BelongsTo
    {
        return $this->belongsTo(Modalidade::class);
    }

    public function tipoproceso(): BelongsTo
    {
        return $this->belongsTo(Tipoproceso::class);
    }

    public function tipoadquisicione(): BelongsTo
    {
        return $this->belongsTo(Tipoadquisicione::class);
    }

    public function requiproyecto(): BelongsTo
    {
        return $this->belongsTo(Requiproyecto::class);
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(Fuente::class);
    }

    public function tipoprioridade(): BelongsTo
    {
        return $this->belongsTo(Tipoprioridade::class);
    }

    public function mese(): BelongsTo
    {
        return $this->belongsTo(Mese::class);
    }

    public function requipoai(): BelongsTo
    {
        return $this->belongsTo(Requipoai::class);
    }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'planadquisicione_producto')->withTimestamps();
    }

    public function clases(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'planadquisicione_producto')->withTimestamps();
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }
}
