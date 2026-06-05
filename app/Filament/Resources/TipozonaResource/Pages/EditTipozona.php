<?php

namespace App\Filament\Resources\TipozonaResource\Pages;

use App\Filament\Resources\TipozonaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipozona extends EditRecord
{
    protected static string $resource = TipozonaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
