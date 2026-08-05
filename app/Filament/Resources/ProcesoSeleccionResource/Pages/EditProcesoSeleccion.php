<?php

namespace App\Filament\Resources\ProcesoSeleccionResource\Pages;

use App\Filament\Resources\ProcesoSeleccionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProcesoSeleccion extends EditRecord
{
    protected static string $resource = ProcesoSeleccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
