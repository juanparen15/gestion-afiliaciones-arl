<?php

namespace App\Filament\Resources\AfiliacionResource\Pages;

use App\Filament\Resources\AfiliacionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ListAfiliacions extends ListRecords
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
        if (Auth::user()->hasRole(['super_admin', 'SSST'])) {
            return true;
        }

        if ($hora >= 17) {
            return false;
        }

        // Permitir desde las 12:01 AM (no permitir justo a las 12:00 AM)
        if ($hora === 0 && $minuto === 0) {
            return false;
        }

        return true;
    }

    protected function getHeaderActions(): array
    {
        $puedeRegistrar = $this->puedeRegistrarAfiliacion();
        $horaActual = Carbon::now()->format('h:i A');

        return [
            Actions\CreateAction::make()
                ->label('Nueva Afiliación')
                ->disabled(!$puedeRegistrar)
                ->tooltip($puedeRegistrar
                    ? null
                    : "El registro de afiliaciones no está disponible después de las 5:00 PM. Hora actual: {$horaActual}. Disponible desde las 12:01 AM del día siguiente."),
        ];
    }
}
