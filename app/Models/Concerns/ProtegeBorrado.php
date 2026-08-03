<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Blindaje de borrado: impide eliminar un registro que tenga otros registros
 * dependientes en la base de datos, con un mensaje claro. Detecta las tablas
 * que referencian a este modelo mediante las llaves foráneas del esquema
 * (information_schema), por lo que protege automáticamente sin configurar cada
 * relación a mano.
 *
 * Un modelo puede permitir el borrado en cascada de ciertos "hijos" legítimos
 * (p. ej. archivos de una afiliación) sobreescribiendo cascadaBorradoPermitida().
 */
trait ProtegeBorrado
{
    public static function bootProtegeBorrado(): void
    {
        static::deleting(function (Model $model) {
            $model->verificarDependenciasAntesDeBorrar();
        });
    }

    /**
     * Tablas hijas cuyo borrado en cascada SÍ se permite (no bloquean).
     * Sobreescribir en el modelo si aplica. Ej: ['archivos_afiliaciones'].
     */
    protected function cascadaBorradoPermitida(): array
    {
        return [];
    }

    /** Etiquetas legibles por nombre de tabla (opcional). */
    protected function etiquetasTablas(): array
    {
        return [
            'areas'                => 'áreas',
            'afiliaciones'         => 'afiliaciones',
            'contratos'            => 'contratos',
            'users'                => 'usuarios',
            'actas_necesidad'      => 'actas de necesidad',
            'planadquisiciones'    => 'planes de adquisición',
            'planadquisicione_producto' => 'ítems de plan',
        ];
    }

    protected function verificarDependenciasAntesDeBorrar(): void
    {
        $conn = $this->getConnection();

        // Solo MySQL: en SQLite (tests) se omite la introspección de FKs.
        if ($conn->getDriverName() !== 'mysql' || ! $this->exists) {
            return;
        }

        $refs = $conn->select(
            'SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE REFERENCED_TABLE_SCHEMA = ? AND REFERENCED_TABLE_NAME = ? AND REFERENCED_COLUMN_NAME = ?',
            [$conn->getDatabaseName(), $this->getTable(), $this->getKeyName()]
        );

        $permitidas = $this->cascadaBorradoPermitida();
        $etiquetas  = $this->etiquetasTablas();
        $bloqueos   = [];

        foreach ($refs as $ref) {
            if (in_array($ref->TABLE_NAME, $permitidas, true)) {
                continue;
            }
            $n = $conn->table($ref->TABLE_NAME)->where($ref->COLUMN_NAME, $this->getKey())->count();
            if ($n > 0) {
                $nombre = $etiquetas[$ref->TABLE_NAME] ?? str_replace('_', ' ', $ref->TABLE_NAME);
                $bloqueos[] = "{$n} {$nombre}";
            }
        }

        if ($bloqueos) {
            $etiqueta = $this->nombre ?? $this->name ?? ('#' . $this->getKey());
            throw new \RuntimeException(
                'No se puede eliminar "' . $etiqueta . '" porque tiene ' . implode(', ', $bloqueos) .
                ' asociados. Reasigne o elimine esos registros primero.'
            );
        }
    }
}
