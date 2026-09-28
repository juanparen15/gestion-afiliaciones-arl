<?php

namespace App\Filament\Resources\ProyectoResource\Pages;

use App\Filament\Resources\ProyectoResource;
use App\Filament\Resources\ProyectoResource\Pages\Concerns\RecalculaValoresProyecto;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProyecto extends EditRecord
{
    use RecalculaValoresProyecto;

    protected static string $resource = ProyectoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->recalcularTodosLosValores($this->record);
        $this->crearItemsEnCatalogo($this->record);
    }
}
