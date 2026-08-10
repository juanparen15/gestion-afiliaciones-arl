<?php

namespace App\Filament\Resources\ContratoRegistroResource\Pages;

use App\Filament\Resources\ContratoRegistroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListContratoRegistros extends ListRecords
{
    protected static string $resource = ContratoRegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
