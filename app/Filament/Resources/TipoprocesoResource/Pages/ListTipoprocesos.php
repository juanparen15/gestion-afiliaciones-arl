<?php

namespace App\Filament\Resources\TipoprocesoResource\Pages;

use App\Filament\Resources\TipoprocesoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoprocesos extends ListRecords
{
    protected static string $resource = TipoprocesoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
