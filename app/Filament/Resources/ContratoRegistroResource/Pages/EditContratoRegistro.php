<?php

namespace App\Filament\Resources\ContratoRegistroResource\Pages;

use App\Filament\Resources\ContratoRegistroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContratoRegistro extends EditRecord
{
    protected static string $resource = ContratoRegistroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
