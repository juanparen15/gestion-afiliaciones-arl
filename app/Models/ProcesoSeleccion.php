<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesoSeleccion extends Model
{
    protected $table = 'procesos_seleccion';

    protected $fillable = [
        'consecutivo',
        'vigencia',
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
        'MINIMA_CUANTIA'     => 'Mínima cuantía',
        'MENOR_CUANTIA'      => 'Menor cuantía',
        'SUBASTA_INVERSA'    => 'Subasta inversa',
        'CONCURSO_MERITOS'   => 'Concurso de méritos',
        'LICITACION_PUBLICA' => 'Licitación pública',
        'LICITACION_OBRA'    => 'Licitación de obra',
    ];

    /** Prefijo del consecutivo por modalidad. */
    public const PREFIJOS = [
        'MINIMA_CUANTIA'     => 'SMC',
        'MENOR_CUANTIA'      => 'SAMC',
        'SUBASTA_INVERSA'    => 'SASI',
        'CONCURSO_MERITOS'   => 'CMA',
        'LICITACION_PUBLICA' => 'LIC',
        'LICITACION_OBRA'    => 'LIC OBRA',
    ];

    protected static function booted(): void
    {
        // Consecutivo automático por modalidad + vigencia (si viene vacío).
        static::creating(function (ProcesoSeleccion $p) {
            if (blank($p->vigencia)) {
                $p->vigencia = optional($p->fecha)->year ?? (int) date('Y');
            }
            if (blank($p->consecutivo)) {
                $p->consecutivo = static::siguienteConsecutivo($p->modalidad, (int) $p->vigencia);
            }
        });
    }

    /** Siguiente consecutivo (3 dígitos) para una modalidad y vigencia. */
    public static function siguienteConsecutivo(string $modalidad, int $vigencia): string
    {
        $max = static::where('modalidad', $modalidad)->where('vigencia', $vigencia)
            ->get(['consecutivo'])
            ->map(fn ($r) => (int) preg_replace('/\D/', '', (string) $r->consecutivo))
            ->max();

        return str_pad((string) (((int) $max) + 1), 3, '0', STR_PAD_LEFT);
    }

    /** Código completo, ej. "SMC 001 DE 2026". */
    public function getCodigoAttribute(): string
    {
        $prefijo = self::PREFIJOS[$this->modalidad] ?? '';
        $num = str_pad((string) (int) preg_replace('/\D/', '', (string) $this->consecutivo), 3, '0', STR_PAD_LEFT);
        $anio = $this->vigencia ?: (optional($this->fecha)->year ?? date('Y'));

        return trim("{$prefijo} {$num} DE {$anio}");
    }

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

        return Planadquisicione::where('vigencia', $anio)->where('id_vigencia', $nreg)->first();
    }
}
