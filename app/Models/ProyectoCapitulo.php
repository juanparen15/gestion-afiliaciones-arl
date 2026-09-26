<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProyectoCapitulo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'proyecto_capitulos';

    protected $fillable = [
        'proyecto_id',
        'numero_capitulo',
        'nombre_capitulo',
        'valor_total',
        'valor_ejecutado',
        'valor_restante',
        'orden',
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'valor_ejecutado' => 'decimal:2',
        'valor_restante' => 'decimal:2',
    ];

    /**
     * Relación: Un capítulo pertenece a un proyecto
     */
    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(Proyecto::class, 'proyecto_id');
    }

    /**
     * Relación: Un capítulo tiene muchos ítems
     */
    public function items(): HasMany
    {
        return $this->hasMany(CapituloItem::class, 'proyecto_capitulo_id')
            ->orderBy('orden');
    }

    /**
     * Calcula y actualiza los valores del capítulo
     */
    public function recalcularValores(): void
    {
        $this->items->each->recalcularValores();

        $this->valor_total = $this->items->sum('valor_total');
        $this->valor_ejecutado = $this->items->sum('valor_ejecutado');
        $this->valor_restante = $this->valor_total - $this->valor_ejecutado;

        $this->save();
    }

    /**
     * Obtiene el nombre del capítulo con número romano
     */
    public function getNombreCompletoAttribute(): string
    {
        $numeroRomano = $this->numeroARomano($this->numero_capitulo);
        return "CAPÍTULO {$numeroRomano}. {$this->nombre_capitulo}";
    }

    /**
     * Convierte número a romano
     */
    private function numeroARomano(int $numero): string
    {
        $map = [
            'M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400,
            'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40,
            'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1
        ];

        $resultado = '';
        foreach ($map as $romano => $valor) {
            while ($numero >= $valor) {
                $resultado .= $romano;
                $numero -= $valor;
            }
        }

        return $resultado;
    }

    /**
     * Boot del modelo
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($capitulo) {
            $capitulo->nombre_capitulo = strtoupper($capitulo->nombre_capitulo);
        });

        static::updating(function ($capitulo) {
            $capitulo->nombre_capitulo = strtoupper($capitulo->nombre_capitulo);
        });

        static::deleting(function ($capitulo) {
            // Eliminar todos los ítems del capítulo
            $capitulo->items()->delete();
        });
    }
}
