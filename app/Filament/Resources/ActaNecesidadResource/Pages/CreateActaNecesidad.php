<?php

namespace App\Filament\Resources\ActaNecesidadResource\Pages;

use App\Filament\Resources\ActaNecesidadResource;
use App\Models\Area;
use App\Models\Dependencia;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class CreateActaNecesidad extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ActaNecesidadResource::class;

    protected function getSteps(): array
    {
        return ActaNecesidadResource::getWizardSteps();
    }

    /** "Crear y crear otro" disponible junto al wizard (Crear y Cancelar van en el último paso). */
    protected function getFormActions(): array
    {
        return [
            $this->getCreateAnotherFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['estado'] = 'pendiente';
        $data['fecha_solicitud'] = now();

        // Asignar el No. de Acta (consecutivo) desde el registro, no en la aprobación
        $data['consecutivo'] = \App\Models\ActaNecesidad::siguienteConsecutivo();

        // Denormalizar nombres para el documento
        $data['dependencia_nombre'] = Dependencia::find($data['dependencia_id'] ?? null)?->nombre;
        $data['area_nombre'] = Area::find($data['area_id'] ?? null)?->nombre;

        return $data;
    }

    protected function afterCreate(): void
    {
        $acta = $this->record;

        User::where('puede_aprobar_actas', true)->each(function (User $aprobador) use ($acta) {
            Notification::make()
                ->info()
                ->title('Nueva solicitud de acta de necesidad')
                ->body("{$acta->nombre_solicitante} ({$acta->dependencia_texto}) registró una solicitud. Requiere revisión.")
                ->actions([
                    \Filament\Notifications\Actions\Action::make('ver')
                        ->label('Ver')
                        ->url(ActaNecesidadResource::getUrl('index')),
                ])
                ->sendToDatabase($aprobador);

            $correo = $aprobador->correo_institucional ?: $aprobador->email;
            if ($correo) {
                try {
                    Mail::to($correo)->send(new \App\Mail\ActaNuevaSolicitudMail($acta));
                } catch (\Throwable $e) {
                    // no bloquear el registro si falla el correo
                }
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
