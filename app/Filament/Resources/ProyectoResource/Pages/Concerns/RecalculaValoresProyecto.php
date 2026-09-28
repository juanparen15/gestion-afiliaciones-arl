<?php

namespace App\Filament\Resources\ProyectoResource\Pages\Concerns;

use App\Models\ItemCatalogo;
use App\Models\Proyecto;

/**
 * Recalcula los valores del proyecto/capítulos/ítems tras crear o editar, y
 * crea automáticamente en el catálogo los ítems nuevos que no tengan un
 * equivalente ya registrado (mismo nombre, unidad y tipo).
 */
trait RecalculaValoresProyecto
{
    protected function recalcularTodosLosValores(Proyecto $proyecto): void
    {
        $proyecto->load(['capitulos.items']);

        foreach ($proyecto->capitulos as $capitulo) {
            $valorTotalCapitulo = 0;
            $valorEjecutadoCapitulo = 0;

            foreach ($capitulo->items as $item) {
                $item->valor_total = $item->valor_unitario * $item->cantidad;
                $item->valor_restante = $item->valor_total - $item->valor_ejecutado;
                $item->saveQuietly();

                $valorTotalCapitulo += $item->valor_total;
                $valorEjecutadoCapitulo += $item->valor_ejecutado;
            }

            $capitulo->valor_total = $valorTotalCapitulo;
            $capitulo->valor_ejecutado = $valorEjecutadoCapitulo;
            $capitulo->valor_restante = $valorTotalCapitulo - $valorEjecutadoCapitulo;
            $capitulo->saveQuietly();
        }

        $proyecto->refresh();
        $proyecto->load(['capitulos']);

        $valorTotalProyecto = $proyecto->capitulos->sum('valor_total');
        $valorEjecutadoProyecto = $proyecto->capitulos->sum('valor_ejecutado');

        $proyecto->valor_total = $valorTotalProyecto;
        $proyecto->valor_ejecutado = $valorEjecutadoProyecto;
        $proyecto->valor_restante = $valorTotalProyecto - $valorEjecutadoProyecto;
        $proyecto->porcentaje_ejecucion = $valorTotalProyecto > 0
            ? ($valorEjecutadoProyecto / $valorTotalProyecto) * 100
            : 0;

        $proyecto->saveQuietly();
    }

    protected function crearItemsEnCatalogo(Proyecto $proyecto): void
    {
        foreach ($proyecto->capitulos as $capitulo) {
            foreach ($capitulo->items as $item) {
                if ($item->item_catalogo_id) {
                    continue;
                }

                $itemCatalogo = ItemCatalogo::where('nombre', 'LIKE', '%' . substr($item->item, 0, 50) . '%')
                    ->where('unidad', $item->unidad)
                    ->where('tipo', $item->tipo)
                    ->first();

                if (! $itemCatalogo) {
                    $codigoBase = 'ITEM-' . strtoupper(substr($item->tipo ?? 'GEN', 0, 3)) . '-';
                    $ultimoCodigo = ItemCatalogo::where('codigo', 'LIKE', $codigoBase . '%')
                        ->orderBy('codigo', 'desc')->first();
                    $numero = $ultimoCodigo ? (int) substr($ultimoCodigo->codigo, -4) + 1 : 1;
                    $codigo = $codigoBase . str_pad((string) $numero, 4, '0', STR_PAD_LEFT);

                    $itemCatalogo = ItemCatalogo::create([
                        'proyecto_origen_id' => $proyecto->id,
                        'codigo' => $codigo,
                        'nombre' => $item->item,
                        'descripcion' => $item->descripcion,
                        'valor_unitario' => $item->valor_unitario,
                        'unidad' => $item->unidad,
                        'cantidad' => $item->cantidad ?? 0,
                        'tipo' => $item->tipo,
                        'etapa' => $item->etapa,
                        'perfil' => $item->perfil,
                        'activo' => true,
                        'valor_ejecutado' => 0,
                        'valor_restante' => 0,
                    ]);
                }

                $item->item_catalogo_id = $itemCatalogo->id;
                $item->saveQuietly();
            }
        }
    }
}
