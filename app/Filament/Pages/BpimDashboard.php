<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\BpimActividadRecienteWidget;
use App\Filament\Widgets\BpimDocumentosStatsOverview;
use App\Filament\Widgets\BpimEstadosChart;
use App\Filament\Widgets\BpimPorDependenciaChart;
use App\Filament\Widgets\BpimPorMesChart;
use App\Filament\Widgets\BpimSolicitudesStatsOverview;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class BpimDashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Gestión de BPIM';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Dashboard BPIM';
    protected static ?int $navigationSort = 0;
    protected static string $routePath = 'bpim-dashboard';

    public function getWidgets(): array
    {
        return [
            BpimSolicitudesStatsOverview::class,
            BpimDocumentosStatsOverview::class,
            BpimEstadosChart::class,
            BpimPorMesChart::class,
            BpimPorDependenciaChart::class,
            BpimActividadRecienteWidget::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return ['default' => 1, 'md' => 2, 'xl' => 2];
    }

    /** Todo el módulo BPIM es exclusivo de super_admin. */
    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }
}
