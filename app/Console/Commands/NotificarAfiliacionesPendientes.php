<?php

namespace App\Console\Commands;

use App\Mail\AfiliacionesPendientesMail;
use App\Models\Afiliacion;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarAfiliacionesPendientes extends Command
{
    protected $signature = 'afiliaciones:notificar-pendientes';

    protected $description = 'Notifica diariamente a usuarios SSST sobre afiliaciones pendientes de validación';

    public function handle(): int
    {
        $this->info('Buscando afiliaciones pendientes de validación...');

        $afiliaciones = Afiliacion::with(['dependencia'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at')
            ->get();

        if ($afiliaciones->isEmpty()) {
            $this->info('No hay afiliaciones pendientes. No se envían notificaciones.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$afiliaciones->count()} afiliación(es) pendiente(s).");

        $usuariosSSST = User::role('SSST')->whereNotNull('email')->get();

        if ($usuariosSSST->isEmpty()) {
            $this->warn('No hay usuarios con rol SSST para notificar.');
            return Command::SUCCESS;
        }

        foreach ($usuariosSSST as $usuario) {
            try {
                Mail::to($usuario->email)
                    ->send(new AfiliacionesPendientesMail($afiliaciones));

                Notification::make()
                    ->title('Afiliaciones pendientes de validación')
                    ->body("Hay {$afiliaciones->count()} afiliación(es) esperando aprobación o rechazo.")
                    ->warning()
                    ->actions([
                        \Filament\Notifications\Actions\Action::make('ver')
                            ->label('Ver pendientes')
                            ->url('/admin/afiliacions?tableFilters[estado][value]=pendiente'),
                    ])
                    ->sendToDatabase($usuario);

                $this->info("Notificación enviada a: {$usuario->email}");
            } catch (\Exception $e) {
                $this->error("Error enviando a {$usuario->email}: {$e->getMessage()}");
            }
        }

        $this->info('Proceso completado.');
        return Command::SUCCESS;
    }
}
