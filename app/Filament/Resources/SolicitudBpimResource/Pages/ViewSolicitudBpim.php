<?php

namespace App\Filament\Resources\SolicitudBpimResource\Pages;

use App\Filament\Resources\SolicitudBpimResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSolicitudBpim extends ViewRecord
{
    protected static string $resource = SolicitudBpimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
