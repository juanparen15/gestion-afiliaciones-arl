<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Filament\Resources\PlanadquisicioneResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewPlanadquisicione extends ViewRecord
{
    protected static string $resource = PlanadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('pantallazo')
                ->label('Tomar pantallazo')
                ->icon('heroicon-o-camera')
                ->color('success')
                ->action(fn () => null)
                ->extraAttributes([
                    'onclick' => 'if (window.tomarPantallazoPlan) { window.tomarPantallazoPlan(); }',
                ]),

            Action::make('editar')
                ->label('Editar')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(fn (): string => PlanadquisicioneResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
