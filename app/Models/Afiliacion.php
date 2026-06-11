<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Afiliacion extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'afiliaciones';

    protected $fillable = [
        'nombre_contratista',
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'tipo_documento',
        'numero_documento',
        'email_contratista',
        'telefono_contratista',
        'fecha_nacimiento',
        'barrio',
        'direccion_residencia',
        'eps',
        'afp',
        'numero_contrato',
        'objeto_contractual',
        'valor_contrato',
        'honorarios_mensual',
        'ibc',
        'fecha_inicio',
        'fecha_fin',
        'meses_contrato',
        'dias_contrato',
        'supervisor_contrato',
        'numero_registro_presupuestal',
        'tiene_adicion',
        'descripcion_adicion',
        'valor_adicion',
        'fecha_adicion',
        'tiene_prorroga',
        'descripcion_prorroga',
        'meses_prorroga',
        'dias_prorroga',
        'nueva_fecha_fin_prorroga',
        'tiene_terminacion_anticipada',
        'fecha_terminacion_anticipada',
        'motivo_terminacion_anticipada',
        'nombre_arl',
        'observaciones_arl',
        'tipo_riesgo',
        'numero_afiliacion_arl',
        'fecha_afiliacion_arl',
        'fecha_terminacion_afiliacion',
        'pdf_arl',
        'pdf_arl_novedad',
        'contrato_pdf_o_word',
        'dependencia_id',
        'area_id',
        'created_by',
        'validated_by',
        'novedad_registrada_por',
        'novedad_registrada_at',
        'estado',
        'observaciones',
        'motivo_rechazo',
        'fecha_validacion',
    ];

    protected $casts = [
        'valor_contrato' => 'decimal:2',
        'honorarios_mensual' => 'decimal:2',
        'ibc' => 'decimal:2',
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'fecha_nacimiento' => 'date',
        'fecha_afiliacion_arl' => 'date',
        'fecha_terminacion_afiliacion' => 'date',
        'fecha_validacion' => 'datetime',
        'novedad_registrada_at' => 'datetime',
        'meses_contrato' => 'integer',
        'dias_contrato' => 'integer',
        'tiene_adicion' => 'boolean',
        'valor_adicion' => 'decimal:2',
        'fecha_adicion' => 'date',
        'tiene_prorroga' => 'boolean',
        'meses_prorroga' => 'integer',
        'dias_prorroga' => 'integer',
        'nueva_fecha_fin_prorroga' => 'date',
        'tiene_terminacion_anticipada' => 'boolean',
        'fecha_terminacion_anticipada' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        // Validar que los honorarios mensuales no sean menores al salario mínimo legal
        static::saving(function ($afiliacion) {
            $salarioMinimo = config('constants.salario_minimo_legal', 1750905); // Valor por defecto si no está en configuración
            if ($afiliacion->honorarios_mensual && $afiliacion->honorarios_mensual < $salarioMinimo) {
                throw new \Exception("Los honorarios mensuales no pueden ser menores al salario mínimo legal vigente en Colombia ($" . number_format($salarioMinimo, 0, ',', '.') . ")");
            }
        });

        // Mantener nombre_contratista (nombre completo) sincronizado a partir de las 4 partes
        static::saving(function ($afiliacion) {
            $partes = array_filter([
                $afiliacion->primer_nombre,
                $afiliacion->segundo_nombre,
                $afiliacion->primer_apellido,
                $afiliacion->segundo_apellido,
            ], fn($p) => filled($p));

            if (! empty($partes)) {
                $afiliacion->nombre_contratista = preg_replace('/\s+/', ' ', trim(implode(' ', $partes)));
            }
        });
    }

    /**
     * Divide un nombre completo en sus 4 partes según la convención colombiana.
     * Heurística (imperfecta, editable por el usuario):
     *   2 palabras → nombre + primer apellido
     *   3 palabras → nombre + primer apellido + segundo apellido
     *   4 palabras → nombre + segundo nombre + primer apellido + segundo apellido
     *   5+         → nombre + segundo nombre + primer apellido + (resto) segundo apellido
     *
     * @return array{primer_nombre: string, segundo_nombre: string, primer_apellido: string, segundo_apellido: string}
     */
    public static function dividirNombre(?string $completo): array
    {
        $vacio = ['primer_nombre' => '', 'segundo_nombre' => '', 'primer_apellido' => '', 'segundo_apellido' => ''];

        $completo = preg_replace('/\s+/', ' ', trim((string) $completo));
        if ($completo === '') {
            return $vacio;
        }

        $palabras = explode(' ', $completo);
        $n = count($palabras);

        return match (true) {
            $n === 1 => ['primer_nombre' => $palabras[0], 'segundo_nombre' => '', 'primer_apellido' => '', 'segundo_apellido' => ''],
            $n === 2 => ['primer_nombre' => $palabras[0], 'segundo_nombre' => '', 'primer_apellido' => $palabras[1], 'segundo_apellido' => ''],
            $n === 3 => ['primer_nombre' => $palabras[0], 'segundo_nombre' => '', 'primer_apellido' => $palabras[1], 'segundo_apellido' => $palabras[2]],
            $n === 4 => ['primer_nombre' => $palabras[0], 'segundo_nombre' => $palabras[1], 'primer_apellido' => $palabras[2], 'segundo_apellido' => $palabras[3]],
            default  => [
                'primer_nombre'    => $palabras[0],
                'segundo_nombre'   => $palabras[1],
                'primer_apellido'  => $palabras[2],
                'segundo_apellido' => implode(' ', array_slice($palabras, 3)),
            ],
        };
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function dependencia(): BelongsTo
    {
        return $this->belongsTo(Dependencia::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function novedadRegistradaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'novedad_registrada_por');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ArchivoAfiliacion::class);
    }

    public function scopePendiente($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeValidado($query)
    {
        return $query->where('estado', 'validado');
    }

    public function scopeRechazado($query)
    {
        return $query->where('estado', 'rechazado');
    }

    public function scopeVigente($query)
    {
        return $query->where('fecha_fin', '>=', now());
    }
}
