<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopedPaaQuery;
use App\Models\Planadquisicione;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\Auth;

class PlanesValorPorDependenciaChart extends ChartWidget
{
    use InteractsWithPageFilters;
    use ScopedPaaQuery;

    protected static ?string $heading = 'Valor estimado por dependencia';
    protected static ?int $sort = 1;
    protected static ?string $maxHeight = '320px';
    protected int | string | array $columnSpan = [
        'default' => 'full',
        'xl' => 3,
    ];

    /** Solo quien ve todos los planes (super_admin/SSST). */
    public static function canView(): bool
    {
        $user = Auth::user();

        return (bool) ($user?->hasRole('super_admin') || $user?->hasRole('SSST'));
    }

    protected function getData(): array
    {
        $plans = $this->planQuery()
            ->with(['dependencia:id,nombre', 'area:id,nombre,dependencia_id', 'area.dependencia:id,nombre'])
            ->get(['id', 'valorestimadocont', 'dependencia_id', 'area_id']);

        $grouped = [];
        foreach ($plans as $p) {
            $dep = $p->dependencia?->nombre
                ?? $p->area?->dependencia?->nombre
                ?? 'Sin dependencia';
            $grouped[$dep] = ($grouped[$dep] ?? 0) + Planadquisicione::parseValor($p->valorestimadocont);
        }

        arsort($grouped);
        $grouped = array_slice($grouped, 0, 12, true);

        return [
            'datasets' => [[
                'label' => 'Valor estimado ($)',
                'data' => array_values($grouped),
                'backgroundColor' => 'rgba(99, 102, 241, 0.8)',
                'borderColor' => 'rgb(79, 70, 229)',
                'borderWidth' => 2,
                'borderRadius' => 4,
            ]],
            'labels' => array_map(
                fn ($n) => mb_strlen((string) $n) > 28 ? mb_substr((string) $n, 0, 25) . '...' : $n,
                array_keys($grouped)
            ),
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
            'scales' => ['x' => ['beginAtZero' => true]],
            'plugins' => ['legend' => ['display' => false]],
            'maintainAspectRatio' => false,
        ];
    }
}
