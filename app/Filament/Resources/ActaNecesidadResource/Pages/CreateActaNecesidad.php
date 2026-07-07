<?php

namespace App\Filament\Resources\ActaNecesidadResource\Pages;

use App\Filament\Resources\ActaNecesidadResource;
use App\Models\Area;
use App\Models\Dependencia;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateActaNecesidad extends CreateRecord
{
    protected static string $resource = ActaNecesidadResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['estado'] = 'pendiente';
        $data['fecha_solicitud'] = now();

        // Denormalizar nombres para el documento
        $data['dependencia_nombre'] = Dependencia::find($data['dependencia_id'] ?? null)?->nombre;
        $data['area_nombre'] = Area::find($data['area_id'] ?? null)?->nombre;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
