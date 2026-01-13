<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class CreateAfiliacion extends CreateRecord
{
    protected static string $resource = AfiliacionResource::class;

    /**
     * Verifica si el registro de afiliaciones está permitido según el horario
     */
    protected function puedeRegistrarAfiliacion(): bool
    {
        $horaActual = Carbon::now();
        $hora = $horaActual->hour;
        $minuto = $horaActual->minute;

        // No permitir registros después de las 5:00 PM (17:00)
        // Permitir desde las 12:01 AM (00:01) hasta las 5:00 PM (17:00)
        if ($hora >= 17) {
            return false;
        }

        // Permitir desde las 12:01 AM (no permitir justo a las 12:00 AM)
        if ($hora === 0 && $minuto === 0) {
            return false;
        }

        return true;
    }

    /**
     * Hook que se ejecuta al montar el componente
     */
    public function mount(): void
    {
        parent::mount();

        if (!$this->puedeRegistrarAfiliacion()) {
            $horaActual = Carbon::now()->format('h:i A');

            Notification::make()
                ->warning()
                ->title('Registro de afiliaciones no disponible')
                ->body("El registro de afiliaciones no está disponible después de las 5:00 PM. Hora actual: {$horaActual}. Por favor, intente nuevamente desde las 12:01 AM del día siguiente. Esta restricción permite contar con un día hábil para registrar la afiliación en el sistema externo de ARL.")
                ->persistent()
                ->send();

            // Redirigir a la lista de afiliaciones
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Verificar nuevamente el horario antes de crear
        if (!$this->puedeRegistrarAfiliacion()) {
            $horaActual = Carbon::now()->format('h:i A');

            Notification::make()
                ->danger()
                ->title('No se puede crear la afiliación')
                ->body("El horario para registrar afiliaciones ha finalizado (después de las 5:00 PM). Hora actual: {$horaActual}. Por favor, intente nuevamente desde las 12:01 AM del día siguiente.")
                ->persistent()
                ->send();

            $this->halt();
        }

        $data['created_by'] = Auth::id();
        $data['estado'] = $data['estado'] ?? 'pendiente';

        // Asignar dependencia del usuario si no se especificó (para rol Dependencia)
        if (!isset($data['dependencia_id']) && Auth::user()?->dependencia_id) {
            $data['dependencia_id'] = Auth::user()->dependencia_id;
        }

        // Asignar área del usuario si no se especificó
        if (!isset($data['area_id']) && Auth::user()?->area_id) {
            $data['area_id'] = Auth::user()->area_id;
        }

        // Asegurar que campos del tab "Estado y Observaciones" estén como null si el usuario no es SSST o super_admin
        if (!Auth::user()->hasRole(['super_admin', 'SSST'])) {
            $data['observaciones'] = $data['observaciones'] ?? null;
            $data['motivo_rechazo'] = $data['motivo_rechazo'] ?? null;
        }

        return $data;
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
