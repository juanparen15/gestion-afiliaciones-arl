<?php

namespace App\Filament\Resources\PolizaResource\Pages;

use App\Filament\Resources\PolizaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoliza extends EditRecord
{
    protected static string $resource = PolizaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
