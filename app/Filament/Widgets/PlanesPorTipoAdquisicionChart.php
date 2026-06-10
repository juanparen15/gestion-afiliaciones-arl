<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopedPaaQuery;
use App\Models\Tipoadquisicione;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class PlanesPorTipoAdquisicionChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ScopedPaaQuery;

    protected static ?string $heading = 'Registros por tipo de adquisición';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 1,
    ];

    protected function getData(): array
    {
        $conteos = $this->planQuery()
            ->select('tipoadquisicione_id', DB::raw('count(*) as total'))
            ->groupBy('tipoadquisicione_id')
            ->pluck('total', 'tipoadquisicione_id');

        $nombres = Tipoadquisicione::pluck('dettipoadquisicion', 'id');

        $labels = [];
        $data = [];
        foreach ($conteos as $tipoId => $total) {
            $labels[] = $tipoId ? ($nombres[$tipoId] ?? "Tipo #{$tipoId}") : 'Sin clasificar';
            $data[] = $total;
        }

        $palette = [
            'rgba(59, 130, 246, 0.85)', 'rgba(16, 185, 129, 0.85)', 'rgba(245, 158, 11, 0.85)',
            'rgba(239, 68, 68, 0.85)', 'rgba(139, 92, 246, 0.85)', 'rgba(236, 72, 153, 0.85)',
            'rgba(20, 184, 166, 0.85)', 'rgba(249, 115, 22, 0.85)',
        ];

        return [
            'datasets' => [[
                'label' => 'Planes',
                'data' => $data,
                'backgroundColor' => array_slice(array_pad($palette, count($data), 'rgba(156,163,175,0.85)'), 0, count($data)),
                'borderWidth' => 0,
            ]],
            'labels' => $labels,
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
