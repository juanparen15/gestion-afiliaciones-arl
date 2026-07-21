<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ActasNecesidadStatsOverview;
use App\Filament\Widgets\ActasPorMesChart;
use App\Filament\Widgets\AfiliacionesPorDependenciaChart;
use App\Filament\Widgets\AfiliacionesPorEstadoChart;
use App\Filament\Widgets\AfiliacionesStatsOverview;
use App\Filament\Widgets\AfiliacionesTendenciaMensualChart;
use App\Filament\Widgets\ContratosPorVencerWidget;
use App\Filament\Widgets\MapaCalorSemanalChart;
use Filament\Actions;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    /**
     * Solo los widgets de Afiliaciones/Contratos. Los widgets del módulo PAA
     * viven en su propia página (PaaDashboard).
     */
    public function getWidgets(): array
    {
        return [
            AfiliacionesStatsOverview::class,
            AfiliacionesPorDependenciaChart::class,
            AfiliacionesPorEstadoChart::class,
            AfiliacionesTendenciaMensualChart::class,
            MapaCalorSemanalChart::class,
            ContratosPorVencerWidget::class,
            ActasNecesidadStatsOverview::class,
            ActasPorMesChart::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('ayuda')
                ->label('Ayuda')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->extraAttributes(['data-tour' => 'help-button-dashboard'])
                ->action(fn () => null)
                ->after(fn () => $this->js('window.iniciarTour()')),
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 2,
            'lg' => 2,
            'xl' => 3,
            '2xl' => 3,
        ];
    }
}
