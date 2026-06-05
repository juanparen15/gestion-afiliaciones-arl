<?php

namespace App\Filament\Resources\RequiproyectoResource\Pages;

use App\Filament\Resources\RequiproyectoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequiproyectos extends ListRecords
{
    protected static string $resource = RequiproyectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
