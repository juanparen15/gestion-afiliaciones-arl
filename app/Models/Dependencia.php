<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    // Scopes
    public function scopeActivo($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Nombre de la dependencia tal como debe aparecer en la columna
     * "Unidad de contratación (referencia)" del formato de SECOP II.
     * Si no hay mapeo, devuelve el nombre propio.
     */
    public function nombreSecop(): string
    {
        $norm = \Illuminate\Support\Str::of((string) $this->nombre)
            ->ascii()->upper()->replaceMatches('/\s+/', ' ')->trim()->value();

        $map = [
            'SECRETARIA DE HACIENDA'                                    => 'Secretaria de Hacienda',
            'SECRETARIA GENERAL Y DE SERVICIOS ADMINISTRATIVOS'         => 'Secretaria General y Servicios Administrativos',
            'BIBILIOTECA MUNICIPAL'                                     => 'Biblioteca Municipal',
            'BIBLIOTECA MUNICIPAL'                                      => 'Biblioteca Municipal',
            'INSPECCION DE TRANSITO Y TRANSPORTE'                       => 'Inspección de Transito',
            'SECRETARIA DE OBRAS PUBLICAS'                             => 'Secretaria Obras Publicas',
            'UNIDAD DE ASISTENCIA TECNICA -UMATA'                       => 'UMATA',
            'UNIDAD DE ASISTENCIA TECNICA - UMATA'                      => 'UMATA',
            'SECRETARIA DE DESARROLLO SOCIAL Y COMUNITARIO'             => 'Secretaria de Desarrollo',
            'SECRETARIA DE GOBIERNO MUNICIPAL Y CONVIVENCIA CIUDADANA'  => 'Secretaria de Gobierno',
            'SECRETARIA DE PLANEACION MUNICIPAL'                        => 'Secretaria de Planeación',
        ];

        return $map[$norm] ?? (string) $this->nombre;
    }
}
