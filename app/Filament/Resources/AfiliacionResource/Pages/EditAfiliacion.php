<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditAfiliacion extends EditRecord
{
    protected static string $resource = AfiliacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Asegurar que campos del tab "Estado y Observaciones" estén como null si el usuario no es SSST o super_admin
        if (!Auth::user()->hasRole(['super_admin', 'SSST'])) {
            // Dependencia puede editar info adicional pero estado vuelve a pendiente
            $data['estado'] = 'pendiente';
            unset($data['observaciones']);
            unset($data['motivo_rechazo']);
        }

        return $data;
    }
}
