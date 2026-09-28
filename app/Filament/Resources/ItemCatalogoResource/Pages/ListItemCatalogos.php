<?php

namespace App\Filament\Resources\ItemCatalogoResource\Pages;

use App\Filament\Resources\ItemCatalogoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListItemCatalogos extends ListRecords
{
    protected static string $resource = ItemCatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
