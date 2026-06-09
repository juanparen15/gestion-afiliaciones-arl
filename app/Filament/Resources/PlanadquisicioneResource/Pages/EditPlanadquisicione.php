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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Garantía: Tipo de Proceso según la cuantía si quedó vacío.
        if (empty($data['tipoproceso_id'])) {
            $data['tipoproceso_id'] = PlanadquisicioneResource::tipoProcesoSegunValor($data['valorestimadocont'] ?? null);
        }

        return $data;
    }
}
