<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Filament\Resources\PlanadquisicioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\EditRecord\Concerns\HasWizard;

class EditPlanadquisicione extends EditRecord
{
    use HasWizard;

    protected static string $resource = PlanadquisicioneResource::class;

    protected function getSteps(): array
    {
        return PlanadquisicioneResource::getWizardSteps();
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
