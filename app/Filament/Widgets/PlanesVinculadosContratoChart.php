<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopedPaaQuery;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class PlanesVinculadosContratoChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ScopedPaaQuery;

    protected static ?string $heading = 'Planes vinculados a contratos';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $total = (clone $this->planQuery())->count();
        $vinculados = (clone $this->planQuery())->has('contratos')->count();
        $sinVincular = max(0, $total - $vinculados);

        return [
            'datasets' => [[
                'label' => 'Planes',
                'data' => [$vinculados, $sinVincular],
                'backgroundColor' => ['rgba(16, 185, 129, 0.85)', 'rgba(156, 163, 175, 0.7)'],
                'borderWidth' => 0,
            ]],
            'labels' => ['Vinculados a contrato', 'Sin vincular'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['position' => 'bottom']],
            'maintainAspectRatio' => false,
        ];
    }
}
