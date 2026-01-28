<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MapaCalorSemanalChart extends ChartWidget
{
    protected static ?string $heading = 'Actividad Semanal de Registros';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '280px';
    protected static ?string $pollingInterval = '60s';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 1,
        'xl' => 1,
    ];

    public ?string $filter = 'mes';

    protected function getFilters(): ?array
    {
        return [
            'semana' => 'Última Semana',
            'mes' => 'Último Mes',
            'trimestre' => 'Último Trimestre',
            'año' => 'Último Año',
        ];
    }

    protected function getData(): array
    {
        $user = Auth::user();

        // Determinar rango de fechas según filtro
        $fechaInicio = match ($this->filter) {
            'semana' => now()->subWeek(),
            'mes' => now()->subMonth(),
            'trimestre' => now()->subMonths(3),
            'año' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Query base con filtros de permisos
        $query = Afiliacion::query()
            ->where('created_at', '>=', $fechaInicio);

        if (!$user->hasRole(['super_admin', 'SSST'])) {
            if ($user->area_id) {
                $query->where('area_id', $user->area_id);
            } else {
                $query->where('dependencia_id', $user->dependencia_id);
            }
        }

        // Agrupar por día de la semana
        // Usamos DAYOFWEEK en MySQL (1=Domingo, 2=Lunes, ..., 7=Sábado)
        $datos = $query
            ->select(DB::raw('DAYOFWEEK(created_at) as dia_semana'), DB::raw('COUNT(*) as total'))
            ->groupBy('dia_semana')
            ->orderBy('dia_semana')
            ->pluck('total', 'dia_semana')
            ->toArray();

        // Días de la semana en español (ordenados Lunes a Domingo)
        $diasSemana = [
            2 => 'Lunes',
            3 => 'Martes',
            4 => 'Miércoles',
            5 => 'Jueves',
            6 => 'Viernes',
            7 => 'Sábado',
            1 => 'Domingo',
        ];

        $labels = [];
        $data = [];
        $colors = [];
        $borderColors = [];

        // Calcular máximo para escala de colores
        $maxValor = max($datos ?: [1]);

        foreach ($diasSemana as $num => $nombre) {
            $labels[] = $nombre;
            $valor = $datos[$num] ?? 0;
            $data[] = $valor;

            // Calcular intensidad del color (más registros = más intenso)
            $intensidad = $maxValor > 0 ? ($valor / $maxValor) : 0;

            // Gradiente de azul claro a azul oscuro
            if ($valor === 0) {
                $colors[] = 'rgba(229, 231, 235, 0.8)'; // gris claro para días sin actividad
                $borderColors[] = 'rgb(209, 213, 219)';
            } else {
                // Interpolación de color: azul claro (bajo) a azul oscuro (alto)
                $r = (int)(219 - ($intensidad * 160)); // 219 -> 59
                $g = (int)(234 - ($intensidad * 104)); // 234 -> 130
                $b = 246; // constante azul
                $colors[] = "rgba({$r}, {$g}, {$b}, 0.85)";
                $borderColors[] = "rgb(" . max(0, $r - 30) . ", " . max(0, $g - 30) . ", " . ($b - 10) . ")";
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Registros',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderColor' => $borderColors,
                    'borderWidth' => 2,
                    'borderRadius' => 6,
                    'borderSkipped' => false,
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
                    'display' => false,
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => null,
                    ],
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
