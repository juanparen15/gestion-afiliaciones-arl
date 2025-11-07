<?php

namespace App\Filament\Resources\DependenciaResource\Pages;

use App\Filament\Resources\DependenciaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDependencia extends EditRecord
{
    protected static string $resource = DependenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
