<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\ScopedPaaQuery;
use App\Models\Planadquisicione;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaaStatsOverview extends BaseWidget
{
    use InteractsWithPageFilters;
    use ScopedPaaQuery;

    protected static ?int $sort = 0;
    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $vigencia = $this->vigenciaFiltro();

        $planes = (clone $this->planQuery())->count();

        // Los valores legacy son string con separador de miles ("55.000.000").
        $valor = (clone $this->planQuery())
            ->pluck('valorestimadocont')
            ->sum(fn ($v) => Planadquisicione::parseValor($v));

        $vinculados = (clone $this->planQuery())->has('contratos')->count();
        $pctVinculados = $planes > 0 ? round($vinculados / $planes * 100) : 0;

        return [
            Stat::make("Planes vigencia {$vigencia}", number_format($planes, 0, ',', '.'))
                ->description('Líneas del plan de adquisiciones')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),
            Stat::make('Valor estimado total', '$ ' . number_format($valor, 0, ',', '.'))
                ->description("Vigencia {$vigencia}")
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('success'),
            Stat::make('Vinculados a contrato', number_format($vinculados, 0, ',', '.'))
                ->description("{$pctVinculados}% de avance")
                ->descriptionIcon('heroicon-o-link')
                ->color($pctVinculados >= 50 ? 'success' : 'warning'),
            Stat::make('Valor promedio', '$ ' . number_format($planes > 0 ? $valor / $planes : 0, 0, ',', '.'))
                ->description('Por línea del plan')
                ->descriptionIcon('heroicon-o-calculator')
                ->color('gray'),
        ];
    }
}
