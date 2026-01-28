<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AfiliacionesTendenciaMensualChart extends ChartWidget
{
    protected static ?string $heading = 'Tendencia de Afiliaciones';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 1,
        'xl' => 2,
    ];

    public ?string $filter = '6';

    protected function getFilters(): ?array
    {
        return [
            '3' => 'Últimos 3 Meses',
            '6' => 'Últimos 6 Meses',
            '12' => 'Último Año',
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();
        $cantidadMeses = (int) $this->filter;

        // Obtener datos de los últimos N meses
        $meses = collect();
        for ($i = $cantidadMeses - 1; $i >= 0; $i--) {
            $meses->push(Carbon::now()->subMonths($i));
        }

        $labels = $meses->map(fn($fecha) => $fecha->translatedFormat('M Y'))->toArray();

        $dataNuevas = [];
        $dataFinalizadas = [];

        foreach ($meses as $mes) {
            $inicioMes = $mes->copy()->startOfMonth();
            $finMes = $mes->copy()->endOfMonth();

            // Query base con filtros de permisos
            $queryNuevas = Afiliacion::whereBetween('created_at', [$inicioMes, $finMes]);
            $queryFinalizadas = Afiliacion::whereBetween('fecha_fin', [$inicioMes, $finMes]);

            if (!$user->hasRole(['super_admin', 'SSST'])) {
                if ($user->area_id) {
                    $queryNuevas->where('area_id', $user->area_id);
                    $queryFinalizadas->where('area_id', $user->area_id);
                } else {
                    $queryNuevas->where('dependencia_id', $user->dependencia_id);
                    $queryFinalizadas->where('dependencia_id', $user->dependencia_id);
                }
            }

            $dataNuevas[] = $queryNuevas->count();
            $dataFinalizadas[] = $queryFinalizadas->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nuevas Afiliaciones',
                    'data' => $dataNuevas,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => 'rgb(59, 130, 246)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
                [
                    'label' => 'Contratos Finalizados',
                    'data' => $dataFinalizadas,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'fill' => true,
                    'tension' => 0.3,
                    'pointBackgroundColor' => 'rgb(239, 68, 68)',
                    'pointBorderColor' => '#fff',
                    'pointBorderWidth' => 2,
                    'pointRadius' => 4,
                ],
            ],
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false,
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
