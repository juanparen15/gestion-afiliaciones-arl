<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoSeleccion extends Model
{
    protected $table = 'procesos_seleccion';

    protected $fillable = [
        'consecutivo',
        'fecha',
        'objeto',
        'modalidad',
        'dependencia_id',
        'dependencia_nombre',
        'consecutivo_paa',
        'planadquisicione_id',
        'elaborador_id',
        'estado',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public const MODALIDADES = [
        'MINIMA_CUANTIA'   => 'Mínima cuantía',
        'MENOR_CUANTIA'    => 'Menor cuantía',
        'SUBASTA_INVERSA'  => 'Subasta inversa',
        'CONCURSO_MERITOS' => 'Concurso de méritos',
        'LICITACION'       => 'Licitación',
    ];

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function elaborador(): BelongsTo
    {
        return $this->belongsTo(Elaborador::class);
    }

    public function planadquisicione(): BelongsTo
    {
        return $this->belongsTo(Planadquisicione::class);
    }

    /** Nombre de dependencia (relación o texto de respaldo). */
    public function getDependenciaTextoAttribute(): string
    {
        return $this->dependencia?->nombre ?: (string) $this->dependencia_nombre;
    }

    public function getModalidadLabelAttribute(): string
    {
        return self::MODALIDADES[$this->modalidad] ?? (string) $this->modalidad;
    }

    /** Vincula (por consecutivo_paa "AÑO-NREG") al registro del Plan de Adquisiciones, si existe. */
    public function resolverPaa(): ?Planadquisicione
    {
        if (! $this->consecutivo_paa || ! preg_match('/(\d{4})\D+(\d+)/', $this->consecutivo_paa, $m)) {
            return null;
        }
        [$anio, $nreg] = [(int) $m[1], (int) $m[2]];

        return Planadquisicione::whereYear('created_at', $anio)->where('id_vigencia', $nreg)->first();
    }
}
