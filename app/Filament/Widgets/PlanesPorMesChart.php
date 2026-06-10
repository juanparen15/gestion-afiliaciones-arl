<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopedPaaQuery;
use App\Models\Mese;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class PlanesPorMesChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ScopedPaaQuery;

    protected static ?string $heading = 'Línea de tiempo: planes por mes de inicio';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $conteos = $this->planQuery()
            ->whereNotNull('mese_id')
            ->select('mese_id', DB::raw('count(*) as total'))
            ->groupBy('mese_id')
            ->pluck('total', 'mese_id');

        // El catálogo meses está en orden cronológico por id.
        $meses = Mese::orderBy('id')->pluck('nommes', 'id');

        $labels = [];
        $data = [];
        foreach ($meses as $id => $nombre) {
            $labels[] = $nombre;
            $data[] = $conteos[$id] ?? 0;
        }

        return [
            'datasets' => [[
                'label' => 'Planes',
                'data' => $data,
                'fill' => true,
                'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                'borderColor' => 'rgb(5, 150, 105)',
                'borderWidth' => 2,
                'tension' => 0.3,
                'pointRadius' => 3,
                'pointBackgroundColor' => 'rgb(5, 150, 105)',
            ]],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => ['y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
            'plugins' => ['legend' => ['display' => false]],
            'maintainAspectRatio' => false,
        ];
    }
}
