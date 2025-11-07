<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArchivoAfiliacion extends Model
{
    protected $table = 'archivos_afiliaciones';

    protected $fillable = [
        'afiliacion_id',
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'tipo_archivo',
        'mime_type',
        'tamano',
        'tipo_documento',
        'descripcion',
        'uploaded_by',
    ];

    protected $casts = [
        'tamano' => 'integer',
    ];

    public function afiliacion(): BelongsTo
    {
        return $this->belongsTo(Afiliacion::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
