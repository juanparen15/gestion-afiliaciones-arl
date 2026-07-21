<?php

namespace App\Console\Commands;

use App\Mail\ActasPendientesRecordatorioMail;
use App\Models\ActaNecesidad;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class RecordarActasPendientes extends Command
{
    protected $signature = 'actas:recordar-pendientes';

    protected $description = 'Notifica a los aprobadores las actas de necesidad pendientes de revisión.';

    public function handle(): int
    {
        $pendientes = ActaNecesidad::where('estado', 'pendiente')
            ->orderBy('fecha_solicitud')
            ->get();

        if ($pendientes->isEmpty()) {
            $this->info('No hay actas pendientes.');
            return self::SUCCESS;
        }

        $aprobadores = User::where('puede_aprobar_actas', true)->get();
        if ($aprobadores->isEmpty()) {
            $this->warn('No hay usuarios con permiso para aprobar actas.');
            return self::SUCCESS;
        }

        foreach ($aprobadores as $aprobador) {
            Notification::make()
                ->warning()
                ->title($pendientes->count() . ' acta(s) de necesidad pendiente(s)')
                ->body('Tiene actas de necesidad esperando revisión.')
                ->sendToDatabase($aprobador);

            $correo = $aprobador->correo_institucional ?: $aprobador->email;
            if ($correo) {
                try {
                    Mail::to($correo)->send(new ActasPendientesRecordatorioMail($pendientes));
                } catch (\Throwable $e) {
                    $this->error("Fallo el correo a {$correo}: " . $e->getMessage());
                }
            }
        }

        $this->info("Recordatorio enviado a {$aprobadores->count()} aprobador(es) por {$pendientes->count()} acta(s) pendiente(s).");
        return self::SUCCESS;
    }
}
