<?php

namespace App\Filament\Resources\SolicitudBpimResource\Pages;

use App\Filament\Resources\SolicitudBpimResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSolicitudBpim extends EditRecord
{
    protected static string $resource = SolicitudBpimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
