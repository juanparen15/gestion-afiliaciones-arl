<?php

namespace App\Filament\Resources\TipoadquisicioneResource\Pages;

use App\Filament\Resources\TipoadquisicioneResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoadquisicione extends EditRecord
{
    protected static string $resource = TipoadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
