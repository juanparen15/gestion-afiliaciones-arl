<?php

namespace App\Filament\Resources\TipoadquisicioneResource\Pages;

use App\Filament\Resources\TipoadquisicioneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTipoadquisiciones extends ListRecords
{
    protected static string $resource = TipoadquisicioneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
