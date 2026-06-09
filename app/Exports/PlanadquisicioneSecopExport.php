<?php

namespace App\Exports;

use App\Models\Planadquisicione;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

/**
 * Exporta los planes de una vigencia en el formato de importación de SECOP II
 * (Colombia Compra Eficiente). Replica el formato del sistema PAA original.
 */
class PlanadquisicioneSecopExport implements FromView
{
    public function __construct(public int $vigencia)
    {
    }

    public function view(): View
    {
        $planadquisiciones = Planadquisicione::query()
            ->with([
                'mese', 'intervalo', 'modalidade', 'fuente', 'vigenfutura',
                'estadovigencia', 'area', 'dependencia', 'user', 'productos', 'clases',
            ])
            ->whereYear('created_at', $this->vigencia)
            ->orderBy('id_vigencia')
            ->get();

        return view('exports.planadquisiciones-secop', [
            'planadquisiciones' => $planadquisiciones,
        ]);
    }
}
