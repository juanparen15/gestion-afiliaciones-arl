<?php

namespace App\Filament\Pages;

use Filament\Actions;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
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
}
