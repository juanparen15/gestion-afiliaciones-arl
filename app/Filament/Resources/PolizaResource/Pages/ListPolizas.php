<?php

namespace App\Filament\Resources\PolizaResource\Pages;

use App\Filament\Resources\PolizaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPolizas extends ListRecords
{
    protected static string $resource = PolizaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
