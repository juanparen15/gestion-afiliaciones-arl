<?php

namespace App\Filament\Resources\TipoprioridadeResource\Pages;

use App\Filament\Resources\TipoprioridadeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoprioridades extends ListRecords
{
    protected static string $resource = TipoprioridadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
