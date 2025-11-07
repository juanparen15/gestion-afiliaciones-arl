<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use App\Models\Dependencia;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AfiliacionesPorDependenciaChart extends ChartWidget
{
    protected static ?string $heading = 'Afiliaciones por Dependencia';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '350px';

    protected function getData(): array
    {
        $user = Auth::user();

        if (!$user->hasRole('super_admin')) {
            // Si no es super_admin, solo mostrar su dependencia
            $dependencia = $user->dependencia;
            $count = Afiliacion::where('dependencia_id', $user->dependencia_id)->count();

            return [
                'datasets' => [
                    [
                        'label' => 'Afiliaciones',
                        'data' => [$count],
                        'backgroundColor' => 'rgb(59, 130, 246)',
                        'borderColor' => 'rgb(37, 99, 235)',
                        'borderWidth' => 2,
                    ],
                ],
                'labels' => [$dependencia->nombre ?? 'Mi Dependencia'],
            ];
        }

        // Para super_admin, mostrar todas las dependencias
        $dependencias = Dependencia::withCount('afiliaciones')
            ->orderBy('afiliaciones_count', 'desc')
            ->limit(10)
            ->get();

        $labels = $dependencias->pluck('nombre')->toArray();
        $data = $dependencias->pluck('afiliaciones_count')->toArray();

        // Generar colores dinámicamente
        $colors = [];
        $borderColors = [];
        foreach ($dependencias as $index => $dep) {
            $hue = ($index * 360 / count($dependencias));
            $colors[] = "hsl({$hue}, 70%, 60%)";
            $borderColors[] = "hsl({$hue}, 70%, 45%)";
        }

        return [
            'datasets' => [
                [
                    'label' => 'Afiliaciones',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                ],
            ],
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
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
        ];
    }
}
