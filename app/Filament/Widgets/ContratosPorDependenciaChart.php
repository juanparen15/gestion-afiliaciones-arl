<?php

namespace App\Filament\Widgets;

use App\Models\ContratoRegistro;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ContratosPorDependenciaChart extends ChartWidget
{
    protected static ?string $heading = 'Valor contratado por dependencia (Top 10)';

    protected static ?int $sort = 1;

    protected static ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = ['default' => 'full', 'xl' => 2];

    protected function getData(): array
    {
        $filas = ContratoRegistro::query()
            ->selectRaw('COALESCE(dependencias.nombre, contrato_registros.dependencia_nombre, "(sin dependencia)") as dep, SUM(contrato_registros.valor) as total')
            ->leftJoin('dependencias', 'dependencias.id', '=', 'contrato_registros.dependencia_id')
            ->groupBy('dep')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Valor contratado',
                'data' => $filas->pluck('total')->map(fn ($v) => (float) $v)->all(),
                'backgroundColor' => '#2563eb',
            ]],
            'labels' => $filas->pluck('dep')->all(),
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
            'plugins' => ['legend' => ['display' => false]],
            'scales' => ['x' => ['beginAtZero' => true]],
        ];
    }
}
