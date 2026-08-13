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
            Action::make('comprobante')
                ->label('Comprobante')
                ->icon('heroicon-o-camera')
                ->color('success')
                ->url(fn (): string => route('plan.comprobante', ['plan' => $this->record]))
                ->openUrlInNewTab(),

            Action::make('editar')
                ->label('Editar')
                ->icon('heroicon-o-pencil-square')
                ->color('gray')
                ->url(fn (): string => PlanadquisicioneResource::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
