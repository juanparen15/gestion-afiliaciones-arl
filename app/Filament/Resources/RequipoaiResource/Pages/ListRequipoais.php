<?php

namespace App\Filament\Resources\RequipoaiResource\Pages;

use App\Filament\Resources\RequipoaiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequipoais extends ListRecords
{
    protected static string $resource = RequipoaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
