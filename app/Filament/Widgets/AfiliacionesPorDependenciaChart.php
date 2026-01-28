<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use App\Models\Dependencia;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class AfiliacionesPorDependenciaChart extends ChartWidget
{
    protected static ?string $heading = 'Afiliaciones por Dependencia';
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 1,
        'xl' => 2,
    ];

    public ?string $filter = 'todas';

    protected function getFilters(): ?array
    {
        return [
            'todas' => 'Todas',
            'vigentes' => 'Vigentes',
            'pendientes' => 'Pendientes',
            'validadas' => 'Validadas',
        ];
    }

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
                        'borderRadius' => 4,
                    ],
                ],
                'labels' => [$this->truncateLabel($dependencia->nombre ?? 'Mi Dependencia')],
            ];
        }

        // Para super_admin, mostrar todas las dependencias con filtro
        $dependencias = Dependencia::withCount(['afiliaciones' => function ($query) {
                if ($this->filter === 'vigentes') {
                    $query->where('fecha_fin', '>=', now());
                } elseif ($this->filter === 'pendientes') {
                    $query->where('estado', 'pendiente');
                } elseif ($this->filter === 'validadas') {
                    $query->where('estado', 'validado');
                }
            }])
            ->orderBy('afiliaciones_count', 'desc')
            ->limit(10)
            ->get();

        // Truncar nombres largos para mejor visualización
        $labels = $dependencias->pluck('nombre')->map(fn($nombre) => $this->truncateLabel($nombre))->toArray();
        $data = $dependencias->pluck('afiliaciones_count')->toArray();

        // Generar colores dinámicamente con mejor paleta
        $colors = [
            'rgba(59, 130, 246, 0.8)',   // blue
            'rgba(16, 185, 129, 0.8)',   // green
            'rgba(245, 158, 11, 0.8)',   // amber
            'rgba(239, 68, 68, 0.8)',    // red
            'rgba(139, 92, 246, 0.8)',   // violet
            'rgba(236, 72, 153, 0.8)',   // pink
            'rgba(20, 184, 166, 0.8)',   // teal
            'rgba(249, 115, 22, 0.8)',   // orange
            'rgba(99, 102, 241, 0.8)',   // indigo
            'rgba(168, 162, 158, 0.8)',  // stone
        ];

        $borderColors = [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(245, 158, 11)',
            'rgb(239, 68, 68)',
            'rgb(139, 92, 246)',
            'rgb(236, 72, 153)',
            'rgb(20, 184, 166)',
            'rgb(249, 115, 22)',
            'rgb(99, 102, 241)',
            'rgb(168, 162, 158)',
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Afiliaciones',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderColor' => array_slice($borderColors, 0, count($data)),
                    'borderWidth' => 2,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    /**
     * Trunca etiquetas largas para mejor visualización
     */
    protected function truncateLabel(string $label, int $maxLength = 25): string
    {
        if (mb_strlen($label) <= $maxLength) {
            return $label;
        }

        return mb_substr($label, 0, $maxLength - 3) . '...';
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y', // Barras horizontales para mejor lectura de nombres largos
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false,
                    ],
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'title' => null, // Se manejará por Chart.js
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
