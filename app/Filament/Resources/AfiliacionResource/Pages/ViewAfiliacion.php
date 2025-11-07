<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAfiliacion extends ViewRecord
{
    protected static string $resource = AfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
