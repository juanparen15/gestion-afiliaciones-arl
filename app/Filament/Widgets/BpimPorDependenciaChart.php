<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BpimPorDependenciaChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 dependencias con más solicitudes BPIM';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    protected function getData(): array
    {
        $datos = SolicitudBpim::select('dependencia', DB::raw('COUNT(*) as total'))
            ->whereNotNull('dependencia')
            ->where('dependencia', '!=', '')
            ->groupBy('dependencia')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $dependencias = $datos->map(fn ($d) => \Illuminate\Support\Str::limit($d->dependencia, 27))->toArray();
        $totales = $datos->pluck('total')->toArray();

        return [
            'datasets' => [[
                'label' => 'Solicitudes',
                'data' => $totales,
                'backgroundColor' => ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
            ]],
            'labels' => $dependencias,
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
            'maintainAspectRatio' => false,
        ];
    }
}
