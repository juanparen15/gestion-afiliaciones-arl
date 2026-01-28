<?php

namespace App\Filament\Widgets;

use App\Models\Afiliacion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AfiliacionesStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3; // 3 columnas en desktop
    }

    protected function getStats(): array
    {
        $user = Auth::user();

        // Filtrar por dependencia si no es super_admin o SST
        $query = Afiliacion::query();
        if (!$user->hasRole(['super_admin', 'SSST'])) {
            if ($user->area_id) {
                $query->where('area_id', $user->area_id);
            } else {
                $query->where('dependencia_id', $user->dependencia_id);
            }
        }

        $total = $query->count();
        $pendientes = (clone $query)->where('estado', 'pendiente')->count();
        $validadas = (clone $query)->where('estado', 'validado')->count();
        $rechazadas = (clone $query)->where('estado', 'rechazado')->count();
        $vigentes = (clone $query)->where('fecha_fin', '>=', now())->count();
        $porVencer = (clone $query)
            ->where('fecha_fin', '>=', now())
            ->where('fecha_fin', '<=', now()->addDays(30))
            ->count();

        return [
            Stat::make('Total Afiliaciones', $total)
                ->description('Total de registros')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary')
                ->chart([7, 12, 15, 18, 22, 25, $total]),

            Stat::make('Pendientes de Validación', $pendientes)
                ->description('Requieren revisión SST')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning')
                ->chart([5, 8, 12, 15, 18, 20, $pendientes]),

            Stat::make('Afiliaciones Validadas', $validadas)
                ->description('Aprobadas por SST')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([3, 5, 8, 12, 15, 18, $validadas]),

            Stat::make('Afiliaciones Rechazadas', $rechazadas)
                ->description('No aprobadas')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart([1, 2, 3, 4, 5, 6, $rechazadas]),

            Stat::make('Contratos Vigentes', $vigentes)
                ->description('Activos actualmente')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->chart([10, 12, 15, 18, 20, 22, $vigentes]),

            Stat::make('Por Vencer (30 días)', $porVencer)
                ->description('Requieren atención')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->chart([2, 3, 4, 5, 6, 7, $porVencer]),
        ];
    }
}
