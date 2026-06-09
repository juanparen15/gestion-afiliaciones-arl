<?php

namespace App\Filament\Resources\ModalidadeResource\Pages;

use App\Filament\Resources\ModalidadeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModalidade extends EditRecord
{
    protected static string $resource = ModalidadeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
