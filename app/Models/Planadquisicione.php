<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planadquisicione extends Model
{
    protected $guarded = [];

    /**
     * Convierte un valor monetario legacy (string con separador de miles,
     * p. ej. "55.000.000") a float.
     */
    public static function parseValor(mixed $valor): float
    {
        return (float) str_replace(['.', ','], ['', '.'], (string) $valor);
    }

    /**
     * Limita la consulta a los planes que el usuario puede ver, replicando la
     * misma lógica de rol que PlanadquisicioneResource::getEloquentQuery().
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        // super_admin y SSST ven todos los planes.
        if ($user->hasRole('super_admin') || $user->hasRole('SSST')) {
            return $query;
        }

        // Con área asignada: solo los planes de su área.
        if ($user->area_id) {
            return $query->where($query->qualifyColumn('area_id'), $user->area_id);
        }

        // Sin área pero con dependencia: planes de su dependencia (directos) o de las áreas de su dependencia.
        if ($user->dependencia_id) {
            return $query->where(function (Builder $q) use ($user) {
                $q->where($q->qualifyColumn('dependencia_id'), $user->dependencia_id)
                    ->orWhereHas('area', fn (Builder $a) => $a->where('dependencia_id', $user->dependencia_id));
            });
        }

        // Sin área ni dependencia: no ve ningún plan.
        return $query->whereRaw('1 = 0');
    }

    /**
     * Aplica los filtros de la barra global del dashboard PAA.
     * Claves soportadas: vigencia, area_id, dependencia_id, tipoadquisicione_id.
     */
    public function scopeApplyDashboardFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['vigencia'])) {
            $query->whereYear($query->qualifyColumn('created_at'), (int) $filters['vigencia']);
        }

        foreach (['area_id', 'dependencia_id', 'tipoadquisicione_id'] as $col) {
            if (! empty($filters[$col])) {
                $query->where($query->qualifyColumn($col), $filters[$col]);
            }
        }

        return $query;
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
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

    /**
     * Líneas de clasificación UNSPSC (clase + producto opcional).
     * Es la relación que gobierna el Repeater del formulario.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PlanadquisicioneProducto::class, 'planadquisicione_id');
    }
}
