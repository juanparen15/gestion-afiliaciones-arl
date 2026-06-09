<?php

namespace App\Filament\Widgets;

use App\Models\{Mese, Planadquisicione};
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PlanesPorMesChart extends ChartWidget
{
    protected static ?string $heading = 'Planes por Mes de Inicio (vigencia actual)';
    protected static ?int $sort = 12;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $vigencia = $this->vigenciaActual();

        $conteos = Planadquisicione::query()
            ->whereYear('created_at', $vigencia)
            ->whereNotNull('mese_id')
            ->select('mese_id', DB::raw('count(*) as total'))
            ->groupBy('mese_id')
            ->pluck('total', 'mese_id');

        // Ordenar por el id del mes (el catálogo meses está en orden cronológico).
        $meses = Mese::orderBy('id')->pluck('nommes', 'id');

        $labels = [];
        $data = [];
        foreach ($meses as $id => $nombre) {
            if (isset($conteos[$id])) {
                $labels[] = $nombre;
                $data[] = $conteos[$id];
            }
        }

        return [
            'datasets' => [[
                'label' => 'Planes',
                'data' => $data,
                'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                'borderColor' => 'rgb(5, 150, 105)',
                'borderWidth' => 2,
                'borderRadius' => 4,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => false]],
            'maintainAspectRatio' => false,
        ];
    }

    private function vigenciaActual(): int
    {
        $latest = Planadquisicione::max('created_at');

        return $latest ? (int) date('Y', strtotime((string) $latest)) : (int) date('Y');
    }
}
