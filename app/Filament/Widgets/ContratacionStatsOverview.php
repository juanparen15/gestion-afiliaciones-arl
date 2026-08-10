<?php

namespace App\Filament\Widgets;

use App\Models\ContratoRegistro;
use App\Models\Poliza;
use App\Models\ProcesoSeleccion;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ContratacionStatsOverview extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getColumns(): int
    {
        return 4;
    }

    protected function getStats(): array
    {
        $procesos = ProcesoSeleccion::count();
        $contratos = ContratoRegistro::count();
        $valor = (float) ContratoRegistro::sum('valor');
        $polizas = Poliza::count();

        return [
            Stat::make('Procesos de Selección', number_format($procesos, 0, ',', '.'))
                ->description('Mínima/menor cuantía, subasta, concurso, licitación')
                ->color('info')->icon('heroicon-o-clipboard-document-list'),

            Stat::make('Contratos / Convenios / Comodatos', number_format($contratos, 0, ',', '.'))
                ->description('Registro unificado')
                ->color('success')->icon('heroicon-o-document-text'),

            Stat::make('Valor contratado', '$' . number_format($valor, 0, ',', '.'))
                ->description('Suma de valores registrados')
                ->color('warning')->icon('heroicon-o-banknotes'),

            Stat::make('Pólizas', number_format($polizas, 0, ',', '.'))
                ->description('Aprobaciones registradas')
                ->color('primary')->icon('heroicon-o-shield-check'),
        ];
    }
}
