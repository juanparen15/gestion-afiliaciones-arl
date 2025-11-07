<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAfiliacion extends EditRecord
{
    protected static string $resource = AfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
