<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Events\AfiliacionCreada;
use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateAfiliacion extends CreateRecord
{
    protected static string $resource = AfiliacionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['estado'] = $data['estado'] ?? 'pendiente';

        // Asignar área del usuario si no se especificó
        if (!isset($data['area_id']) && Auth::user()?->area_id) {
            $data['area_id'] = Auth::user()->area_id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        // Disparar evento para notificar a usuarios SSST
        event(new AfiliacionCreada($this->record));
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Afiliación creada exitosamente';
    }
}
