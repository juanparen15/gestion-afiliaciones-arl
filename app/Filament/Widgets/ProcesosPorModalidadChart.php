<?php

namespace App\Filament\Widgets;

use App\Models\ProcesoSeleccion;
use Filament\Widgets\ChartWidget;

class ProcesosPorModalidadChart extends ChartWidget
{
    protected static ?string $heading = 'Procesos por modalidad';

    protected static ?int $sort = 3;

    protected static ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 1];

    protected function getData(): array
    {
        $filas = ProcesoSeleccion::query()
            ->selectRaw('modalidad, COUNT(*) c')
            ->groupBy('modalidad')
            ->orderByDesc('c')
            ->get();

        return [
            'datasets' => [[
                'label' => 'Procesos',
                'data' => $filas->pluck('c')->all(),
                'backgroundColor' => ['#2563eb', '#16a34a', '#f59e0b', '#7c3aed', '#dc2626'],
            ]],
            'labels' => $filas->pluck('modalidad')->map(fn ($m) => ProcesoSeleccion::MODALIDADES[$m] ?? $m)->all(),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
