<?php

namespace App\Filament\Resources\MeseResource\Pages;

use App\Filament\Resources\MeseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMeses extends ListRecords
{
    protected static string $resource = MeseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
