<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AfiliacionesPorEstadoChart extends ChartWidget
{
    protected static ?string $heading = 'Afiliaciones por Estado';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 1,
        'xl' => 1,
    ];

    public ?string $filter = 'todas';

    protected function getFilters(): ?array
    {
        return [
            'todas' => 'Todas las Afiliaciones',
            'vigentes' => 'Solo Vigentes',
            'vencidas' => 'Solo Vencidas',
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();

        // Query base con filtros de permisos
        $baseQuery = Afiliacion::query();
        if (!$user->hasRole(['super_admin', 'SSST'])) {
            if ($user->area_id) {
                $baseQuery->where('area_id', $user->area_id);
            } else {
                $baseQuery->where('dependencia_id', $user->dependencia_id);
            }
        }

        // Aplicar filtro de vigencia
        if ($this->filter === 'vigentes') {
            $baseQuery->where('fecha_fin', '>=', now());
        } elseif ($this->filter === 'vencidas') {
            $baseQuery->where('fecha_fin', '<', now());
        }

        // Clonar antes de cada consulta para evitar interferencias
        $pendientes = (clone $baseQuery)->where('estado', 'pendiente')->count();
        $validadas = (clone $baseQuery)->where('estado', 'validado')->count();
        $rechazadas = (clone $baseQuery)->where('estado', 'rechazado')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Afiliaciones',
                    'data' => [$pendientes, $validadas, $rechazadas],
                    'backgroundColor' => [
                        'rgba(234, 179, 8, 0.8)',  // warning - pendientes
                        'rgba(34, 197, 94, 0.8)',  // success - validadas
                        'rgba(239, 68, 68, 0.8)',  // danger - rechazadas
                    ],
                    'borderColor' => [
                        'rgb(202, 138, 4)',
                        'rgb(21, 128, 61)',
                        'rgb(185, 28, 28)',
                    ],
                    'borderWidth' => 2,
                    'hoverOffset' => 10,
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
