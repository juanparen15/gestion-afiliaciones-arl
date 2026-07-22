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
        $vigencia = now()->year;
        $ultimo = Planadquisicione::whereYear('created_at', $vigencia)->max('id_vigencia') ?? 0;
        $data['id_vigencia'] = $ultimo + 1;

        // Garantía: Tipo de Proceso según la cuantía si no quedó seleccionado.
        if (empty($data['tipoproceso_id'])) {
            $data['tipoproceso_id'] = PlanadquisicioneResource::tipoProcesoSegunValor($data['valorestimadocont'] ?? null);
        }

        return $data;
    }
}
