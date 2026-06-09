<?php

namespace App\Filament\Widgets;

use App\Models\{Area, Planadquisicione, Producto, User};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaaStatsOverview extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        // Vigencia = año más reciente con datos (derivado de created_at), driver-agnóstico.
        $latest = Planadquisicione::max('created_at');
        $vigencia = $latest ? (int) date('Y', strtotime((string) $latest)) : (int) date('Y');

        $planes = Planadquisicione::whereYear('created_at', $vigencia)->count();

        // Los valores legacy son string con separador de miles ("55.000.000").
        $valor = Planadquisicione::whereYear('created_at', $vigencia)
            ->pluck('valorestimadocont')
            ->sum(fn ($v) => (float) str_replace(['.', ','], ['', '.'], (string) $v));

        return [
            Stat::make("Planes vigencia {$vigencia}", number_format($planes, 0, ',', '.'))
                ->description('Líneas del plan de adquisiciones')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
            Stat::make('Valor estimado total', '$ ' . number_format($valor, 0, ',', '.'))
                ->description("Vigencia {$vigencia}")
                ->color('success'),
            Stat::make('Productos UNSPSC', number_format(Producto::count(), 0, ',', '.'))
                ->description('Catálogo de clasificación')
                ->color('gray'),
            Stat::make('Áreas', Area::count()),
            Stat::make('Usuarios', User::count()),
        ];
    }
}
