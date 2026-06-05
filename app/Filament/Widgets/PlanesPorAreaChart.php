<?php

namespace App\Filament\Widgets;

use App\Models\Planadquisicione;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PlanesPorAreaChart extends ChartWidget
{
    protected static ?string $heading = 'Planes por Área (vigencia actual)';
    protected static ?int $sort = 11;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 2,
    ];

    protected function getData(): array
    {
        $vigencia = $this->vigenciaActual();

        $rows = Planadquisicione::query()
            ->whereYear('planadquisiciones.created_at', $vigencia)
            ->join('areas', 'planadquisiciones.area_id', '=', 'areas.id')
            ->select('areas.nombre', DB::raw('count(*) as total'))
            ->groupBy('areas.nombre')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Planes',
                'data' => $rows->pluck('total')->toArray(),
                'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                'borderColor' => 'rgb(37, 99, 235)',
                'borderWidth' => 2,
                'borderRadius' => 4,
            ]],
            'labels' => $rows->pluck('nombre')->map(fn ($n) => mb_strlen((string) $n) > 25 ? mb_substr((string) $n, 0, 22) . '...' : $n)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => ['x' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]]],
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
