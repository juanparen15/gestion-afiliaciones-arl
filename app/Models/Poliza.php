<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Poliza extends Model
{
    protected $table = 'polizas';

    protected $fillable = [
        'consecutivo',
        'fecha',
        'contrato_texto',
        'contrato_registro_id',
        'estado',
        'dependencia_id',
        'dependencia_nombre',
        'aprobador_id',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function aprobador(): BelongsTo
    {
        return $this->belongsTo(Elaborador::class, 'aprobador_id');
    }

    public function contratoRegistro(): BelongsTo
    {
        return $this->belongsTo(ContratoRegistro::class);
    }

    public function getDependenciaTextoAttribute(): string
    {
        return $this->dependencia?->nombre ?: (string) $this->dependencia_nombre;
    }

    /**
     * Intenta ubicar el contrato (registro) referenciado por el texto
     * "N° de AÑO" (ej. "468 de 2025"): por número + año de la fecha.
     */
    public static function resolverContrato(?string $texto): ?int
    {
        if (! $texto || ! preg_match('/(\d+)\D+(\d{4})/', $texto, $m)) {
            return null;
        }
        $num = ltrim($m[1], '0');
        $anio = (int) $m[2];

        return ContratoRegistro::whereYear('fecha', $anio)
            ->get(['id', 'numero'])
            ->first(fn ($r) => ltrim((string) $r->numero, '0') === $num)?->id;
    }
}
