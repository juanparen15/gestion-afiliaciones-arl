<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Filament\Resources\PlanadquisicioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPlanadquisicione extends EditRecord
{
    protected static string $resource = PlanadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
