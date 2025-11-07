<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Area extends Model
{
    use LogsActivity;

    protected $table = 'areas';

    protected $fillable = [
        'dependencia_id',
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['nombre', 'codigo', 'descripcion', 'responsable', 'email', 'telefono', 'activo'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function afiliaciones(): HasMany
    {
        return $this->hasMany(Afiliacion::class);
    }

    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }
}
