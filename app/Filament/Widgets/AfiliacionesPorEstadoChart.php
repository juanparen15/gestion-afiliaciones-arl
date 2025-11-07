<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AfiliacionesPorEstadoChart extends ChartWidget
{
    protected static ?string $heading = 'Afiliaciones por Estado';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $user = Auth::user();
        $query = Afiliacion::query();

        if (!$user->hasRole('super_admin')) {
            $query->where('dependencia_id', $user->dependencia_id);
        }

        $pendientes = $query->where('estado', 'pendiente')->count();
        $validadas = (clone $query)->where('estado', 'validado')->count();
        $rechazadas = (clone $query)->where('estado', 'rechazado')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Afiliaciones',
                    'data' => [$pendientes, $validadas, $rechazadas],
                    'backgroundColor' => [
                        'rgb(234, 179, 8)', // warning
                        'rgb(34, 197, 94)', // success
                        'rgb(239, 68, 68)',  // danger
                    ],
                    'borderColor' => [
                        'rgb(202, 138, 4)',
                        'rgb(21, 128, 61)',
                        'rgb(185, 28, 28)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
            'labels' => ['Pendientes', 'Validadas', 'Rechazadas'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
