<?php

namespace App\Filament\Resources\ProyectoResource\Pages;

use App\Filament\Resources\ProyectoResource;
use App\Filament\Resources\ProyectoResource\Pages\Concerns\RecalculaValoresProyecto;
use Filament\Resources\Pages\CreateRecord;

class CreateProyecto extends CreateRecord
{
    use RecalculaValoresProyecto;

    protected static string $resource = ProyectoResource::class;

    protected function afterCreate(): void
    {
        $this->recalcularTodosLosValores($this->record);
        $this->crearItemsEnCatalogo($this->record);
    }
}
