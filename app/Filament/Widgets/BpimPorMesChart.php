<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BpimPorMesChart extends ChartWidget
{
    protected static ?string $heading = 'Solicitudes BPIM por mes (últimos 12 meses)';
    protected static ?int $sort = 4;
    protected static ?string $maxHeight = '300px';
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 11; $i >= 0; $i--) {
            $mes = now()->startOfMonth()->subMonths($i);
            $labels[] = $mes->translatedFormat('M Y');
            $data[] = SolicitudBpim::whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->count();
        }

        return [
            'datasets' => [[
                'label' => 'Solicitudes registradas',
                'data' => $data,
                'backgroundColor' => 'rgba(37, 99, 235, 0.15)',
                'borderColor' => 'rgb(37, 99, 235)',
                'borderWidth' => 2,
                'tension' => 0.3,
                'fill' => true,
                'pointRadius' => 3,
                'pointBackgroundColor' => 'rgb(37, 99, 235)',
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
