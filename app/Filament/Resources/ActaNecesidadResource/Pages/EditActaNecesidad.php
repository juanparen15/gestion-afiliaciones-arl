<?php

namespace App\Filament\Resources\ActaNecesidadResource\Pages;

use App\Filament\Resources\ActaNecesidadResource;
use App\Models\Area;
use App\Models\Dependencia;
use Filament\Resources\Pages\EditRecord;

class EditActaNecesidad extends EditRecord
{
    protected static string $resource = ActaNecesidadResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['dependencia_nombre'] = Dependencia::find($data['dependencia_id'] ?? null)?->nombre;
        $data['area_nombre'] = Area::find($data['area_id'] ?? null)?->nombre;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
