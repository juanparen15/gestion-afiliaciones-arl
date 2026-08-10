<?php

namespace App\Filament\Widgets;

use App\Models\Poliza;
use Filament\Widgets\ChartWidget;

class PolizasPorEstadoChart extends ChartWidget
{
    protected static ?string $heading = 'Pólizas por estado';

    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 1];

    protected function getData(): array
    {
        $filas = Poliza::query()
            ->selectRaw('COALESCE(NULLIF(estado, ""), "(sin estado)") as estado, COUNT(*) c')
            ->groupBy('estado')
            ->orderByDesc('c')
            ->limit(8)
            ->get();

        $colores = ['#16a34a', '#2563eb', '#f59e0b', '#dc2626', '#7c3aed', '#0891b2', '#65a30d', '#9ca3af'];

        return [
            'datasets' => [[
                'label' => 'Pólizas',
                'data' => $filas->pluck('c')->all(),
                'backgroundColor' => array_slice($colores, 0, $filas->count()),
            ]],
            'labels' => $filas->pluck('estado')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
