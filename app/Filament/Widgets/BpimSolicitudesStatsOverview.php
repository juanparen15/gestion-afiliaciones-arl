<?php

namespace App\Filament\Widgets;

use App\Models\SolicitudBpim;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class BpimSolicitudesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    public static function canView(): bool
    {
        return Auth::user()?->hasRole('super_admin') ?? false;
    }

    protected function getStats(): array
    {
        $base = SolicitudBpim::query();

        $total = (clone $base)->count();
        $pendientes = (clone $base)->where('aprobar', true)->where('aprobado', false)->where('rechazado', false)->count();
        $aprobadas = (clone $base)->where('aprobado', true)->count();
        $rechazadas = (clone $base)->where('rechazado', true)->count();
        $valorAprobado = (clone $base)->where('aprobado', true)->sum('valor_cdp');

        return [
            Stat::make('Total de solicitudes', number_format($total, 0, ',', '.'))
                ->description('Registradas en el sistema')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Pendientes', number_format($pendientes, 0, ',', '.'))
                ->description('Esperando aprobación')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendientes > 0 ? 'warning' : 'gray'),

            Stat::make('Aprobadas', number_format($aprobadas, 0, ',', '.'))
                ->description('Valor: $' . number_format($valorAprobado, 0, ',', '.'))
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Rechazadas', number_format($rechazadas, 0, ',', '.'))
                ->description('Solicitudes no aprobadas')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
