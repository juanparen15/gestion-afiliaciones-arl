<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Filament\Resources\PlanadquisicioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlanadquisiciones extends ListRecords
{
    protected static string $resource = PlanadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
