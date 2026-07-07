<?php

namespace App\Filament\Resources\ActaNecesidadResource\Pages;

use App\Filament\Resources\ActaNecesidadResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActaNecesidads extends ListRecords
{
    protected static string $resource = ActaNecesidadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nueva solicitud'),
        ];
    }
}
