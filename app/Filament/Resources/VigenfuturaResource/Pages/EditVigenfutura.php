<?php

namespace App\Filament\Resources\VigenfuturaResource\Pages;

use App\Filament\Resources\VigenfuturaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVigenfutura extends EditRecord
{
    protected static string $resource = VigenfuturaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
