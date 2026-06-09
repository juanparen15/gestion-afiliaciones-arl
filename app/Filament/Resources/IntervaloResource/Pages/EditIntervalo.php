<?php

namespace App\Filament\Resources\IntervaloResource\Pages;

use App\Filament\Resources\IntervaloResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditIntervalo extends EditRecord
{
    protected static string $resource = IntervaloResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
