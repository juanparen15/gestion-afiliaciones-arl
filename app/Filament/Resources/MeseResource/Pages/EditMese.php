<?php

namespace App\Filament\Resources\MeseResource\Pages;

use App\Filament\Resources\MeseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMese extends EditRecord
{
    protected static string $resource = MeseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
