<?php

namespace App\Filament\Resources\RequiproyectoResource\Pages;

use App\Filament\Resources\RequiproyectoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequiproyecto extends EditRecord
{
    protected static string $resource = RequiproyectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
