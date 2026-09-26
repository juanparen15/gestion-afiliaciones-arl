<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentoVerificacion extends Model
{
    use HasFactory;

    protected $table = 'documentos_verificacion';

    protected $fillable = [
        'solicitud_bpim_id',
        'codigo_verificacion',
        'hash_documento',
        'fecha_firma',
        'firmado_por',
        'ip_firma',
        'metadata',
        'veces_verificado',
        'ultima_verificacion',
        'activo',
    ];

    protected $casts = [
        'fecha_firma' => 'datetime',
        'ultima_verificacion' => 'datetime',
        'activo' => 'boolean',
        'metadata' => 'array',
    ];

    public function solicitud(): BelongsTo
    {
        return $this->belongsTo(SolicitudBpim::class, 'solicitud_bpim_id');
    }

    /**
     * Genera un código único de verificación
     */
    public static function generarCodigoVerificacion(): string
    {
        do {
            $codigo = strtoupper(bin2hex(random_bytes(16))); // 32 caracteres hexadecimales
        } while (self::where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    /**
     * Registra una verificación
     */
    public function registrarVerificacion(): void
    {
        $this->increment('veces_verificado');
        $this->update(['ultima_verificacion' => now()]);
    }

    /**
     * Obtiene la URL de verificación
     */
    public function getUrlVerificacion(): string
    {
        return route('verificar.documento', ['codigo' => $this->codigo_verificacion]);
    }
}