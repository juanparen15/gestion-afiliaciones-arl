<?php

namespace App\Filament\Resources\VigenfuturaResource\Pages;

use App\Filament\Resources\VigenfuturaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVigenfuturas extends ListRecords
{
    protected static string $resource = VigenfuturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
