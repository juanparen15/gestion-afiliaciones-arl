<?php

namespace App\Filament\Resources\EstadovigenciaResource\Pages;

use App\Filament\Resources\EstadovigenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEstadovigencias extends ListRecords
{
    protected static string $resource = EstadovigenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
