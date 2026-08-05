<?php

namespace App\Filament\Resources\ElaboradorResource\Pages;

use App\Filament\Resources\ElaboradorResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElaboradors extends ListRecords
{
    protected static string $resource = ElaboradorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
