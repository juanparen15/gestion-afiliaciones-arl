<?php

namespace App\Filament\Resources\AreaResource\Pages;

use App\Filament\Resources\AreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAreas extends ListRecords
{
    protected static string $resource = AreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->extraAttributes(['data-tour' => 'create-button-areas']),

            Actions\Action::make('ayuda')
                ->label('Ayuda')
                ->icon('heroicon-o-question-mark-circle')
                ->color('gray')
                ->extraAttributes(['data-tour' => 'help-button-areas'])
                ->action(fn () => null)
                ->after(fn () => $this->js('window.iniciarTour()')),
        ];
    }
}
