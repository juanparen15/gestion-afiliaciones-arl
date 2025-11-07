<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Dependencia extends Model
{
    use LogsActivity;

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'responsable',
        'email',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'codigo', 'descripcion', 'responsable', 'email', 'telefono', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relaciones
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function areas(): HasMany
    {
        return $this->hasMany(Area::class);
    }

    public function afiliaciones(): HasMany
    {
        return $this->hasMany(Afiliacion::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
