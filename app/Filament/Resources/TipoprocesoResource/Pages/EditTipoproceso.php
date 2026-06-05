<?php

namespace App\Filament\Resources\TipoprocesoResource\Pages;

use App\Filament\Resources\TipoprocesoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoproceso extends EditRecord
{
    protected static string $resource = TipoprocesoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
