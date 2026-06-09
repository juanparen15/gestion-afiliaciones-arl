<?php

namespace App\Filament\Resources\IntervaloResource\Pages;

use App\Filament\Resources\IntervaloResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIntervalos extends ListRecords
{
    protected static string $resource = IntervaloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
