<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ConfiguracionActa extends Model
{
    protected $table = 'acta_configuraciones';

    protected $fillable = [
        'label_alcalde',
        'firma_alcalde_path',
    ];

    /** Devuelve (o crea) la fila única de configuración. */
    public static function actual(): self
    {
        return static::first() ?? static::create([
            'label_alcalde' => 'Vo Bo. Alcalde Municipal',
        ]);
    }

    /** Ruta absoluta de la firma del alcalde, o null. */
    public function firmaAbsoluta(): ?string
    {
        if (! $this->firma_alcalde_path) {
            return null;
        }
        $abs = Storage::disk('public')->path($this->firma_alcalde_path);
        return is_file($abs) ? $abs : null;
    }
}
