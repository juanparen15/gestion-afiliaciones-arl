<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ContratacionStatsOverview;
use App\Filament\Widgets\ContratosPorDependenciaChart;
use App\Filament\Widgets\PolizasPorEstadoChart;
use App\Filament\Widgets\ProcesosPorModalidadChart;
use Filament\Pages\Dashboard as BaseDashboard;

class DashboardContratacion extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?string $navigationGroup = 'Contratación';

    protected static ?string $navigationLabel = 'Dashboard';

    protected static ?string $title = 'Dashboard de Contratación';

    protected static ?int $navigationSort = 0;

    protected static string $routePath = 'contratacion-dashboard';

    public function getWidgets(): array
    {
        return [
            ContratacionStatsOverview::class,
            ContratosPorDependenciaChart::class,
            PolizasPorEstadoChart::class,
            ProcesosPorModalidadChart::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }
}
