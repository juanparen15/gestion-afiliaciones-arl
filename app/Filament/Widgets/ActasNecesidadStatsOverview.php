<?php

namespace App\Filament\Widgets;

use App\Models\ActaNecesidad;
use Illuminate\Support\Facades\Auth;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActasNecesidadStatsOverview extends BaseWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    public static function canView(): bool
    {
        $u = Auth::user();
        return $u && ($u->puede_aprobar_actas || $u->hasRole('super_admin'));
    }

    protected function getStats(): array
    {
        $base = ActaNecesidad::query();

        $total     = (clone $base)->count();
        $pendiente = (clone $base)->where('estado', 'pendiente')->count();
        $aprobado  = (clone $base)->where('estado', 'aprobado')->count();
        $mes       = (clone $base)->whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)->count();

        return [
            Stat::make('Total de actas', number_format($total, 0, ',', '.'))
                ->description('Solicitudes registradas')
                ->descriptionIcon('heroicon-o-document-check')
                ->color('primary'),
            Stat::make('Pendientes', number_format($pendiente, 0, ',', '.'))
                ->description('Esperando revisión')
                ->descriptionIcon('heroicon-o-clock')
                ->color($pendiente > 0 ? 'warning' : 'gray'),
            Stat::make('Aprobadas', number_format($aprobado, 0, ',', '.'))
                ->description('Actas emitidas')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
            Stat::make('Registradas este mes', number_format($mes, 0, ',', '.'))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-o-calendar-days')
                ->color('info'),
        ];
    }
}
