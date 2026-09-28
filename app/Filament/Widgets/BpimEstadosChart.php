<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class BpimEstadosChart extends ChartWidget
{
    protected static ?string $heading = 'Solicitudes BPIM por estado';
    protected static ?int $sort = 3;
    protected static ?string $maxHeight = '260px';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    protected function getData(): array
    {
        $borradores = SolicitudBpim::where('aprobar', false)->where('aprobado', false)->count();
        $pendientes = SolicitudBpim::where('aprobar', true)->where('aprobado', false)->where('rechazado', false)->count();
        $aprobadas = SolicitudBpim::where('aprobado', true)->count();
        $rechazadas = SolicitudBpim::where('rechazado', true)->count();

        return [
            'datasets' => [[
                'label' => 'Solicitudes',
                'data' => [$borradores, $pendientes, $aprobadas, $rechazadas],
                'backgroundColor' => ['#6b7280', '#f59e0b', '#10b981', '#ef4444'],
            ]],
            'labels' => ['Borradores', 'Pendientes', 'Aprobadas', 'Rechazadas'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => ['legend' => ['display' => true, 'position' => 'bottom']],
            'maintainAspectRatio' => false,
        ];
    }
}
