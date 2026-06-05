<?php

namespace App\Filament\Resources\RequipoaiResource\Pages;

use App\Filament\Resources\RequipoaiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRequipoai extends EditRecord
{
    protected static string $resource = RequipoaiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
