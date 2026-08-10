<?php

namespace App\Filament\Resources\PlanadquisicioneResource\Pages;

use App\Filament\Resources\PlanadquisicioneResource;
use App\Models\Planadquisicione;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Auth;

class CreatePlanadquisicione extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PlanadquisicioneResource::class;

    protected function getSteps(): array
    {
        return PlanadquisicioneResource::getWizardSteps();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateAnotherFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Registrar quién crea el plan.
        $data['user_id'] ??= Auth::id();

        // N° de Registro: correlativo que se reinicia a 1 en cada vigencia (año).
        $vigencia = (int) ($data['vigencia'] ?? now()->year);
        $ultimo = Planadquisicione::where('vigencia', $vigencia)->max('id_vigencia') ?? 0;
        $data['id_vigencia'] = $ultimo + 1;

        return $data;
    }
}
