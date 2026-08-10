<?php

namespace App\Models;

use App\Models\Concerns\ProtegeBorrado;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContratoRegistro extends Model
{
    use ProtegeBorrado;

    protected $table = 'contrato_registros';

    protected $fillable = [
        'tipo',
        'numero',
        'fecha',
        'contratista',
        'proceso_texto',
        'proceso_seleccion_id',
        'modalidad',
        'dependencia_id',
        'dependencia_nombre',
        'consecutivo_paa',
        'planadquisicione_id',
        'elaborador_id',
        'valor',
        'observaciones',
        'contrato_secop_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2',
    ];

    public const TIPOS = [
        'CONTRATO' => 'Contrato',
        'CONVENIO' => 'Convenio',
        'COMODATO' => 'Comodato',
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

    public function procesoSeleccion(): BelongsTo
    {
        return $this->belongsTo(ProcesoSeleccion::class);
    }

    public function contratoSecop(): BelongsTo
    {
        return $this->belongsTo(Contrato::class, 'contrato_secop_id');
    }

    public function getDependenciaTextoAttribute(): string
    {
        return $this->dependencia?->nombre ?: (string) $this->dependencia_nombre;
    }

    public function getTipoLabelAttribute(): string
    {
        return self::TIPOS[$this->tipo] ?? (string) $this->tipo;
    }

    /** Registro del Plan de Adquisiciones por consecutivo_paa "AÑO-N° Reg", si existe. */
    public function resolverPaa(): ?Planadquisicione
    {
        if (! $this->consecutivo_paa || ! preg_match('/(\d{4})\D+(\d+)/', $this->consecutivo_paa, $m)) {
            return null;
        }

        return Planadquisicione::where('vigencia', (int) $m[1])->where('id_vigencia', (int) $m[2])->first();
    }
}
