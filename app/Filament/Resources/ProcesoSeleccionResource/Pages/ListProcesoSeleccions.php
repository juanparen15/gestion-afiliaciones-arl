<?php

namespace App\Filament\Resources\ProcesoSeleccionResource\Pages;

use App\Filament\Resources\ProcesoSeleccionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProcesoSeleccions extends ListRecords
{
    protected static string $resource = ProcesoSeleccionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
