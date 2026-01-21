<?php

namespace App\Filament\Resources\DependenciaResource\Pages;

use App\Filament\Resources\DependenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDependencias extends ListRecords
{
    protected static string $resource = DependenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->extraAttributes(['data-tour' => 'create-button-dependencias']),

            Actions\Action::make('ayuda')
                ->label('Ayuda')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->extraAttributes(['data-tour' => 'help-button-dependencias'])
                ->action(fn () => null)
                ->after(fn () => $this->js('window.iniciarTour()')),
        ];
    }
}
