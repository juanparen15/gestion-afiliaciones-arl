<?php

namespace App\Listeners;

use App\Events\AfiliacionCreada;
use App\Mail\NuevaAfiliacionMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;

class EnviarNotificacionNuevaAfiliacion
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AfiliacionCreada $event): void
    {
        // Obtener todos los usuarios con rol SSST
        $usuariosSSST = User::role('SSST')->get();

        $afiliacion = $event->afiliacion;

        // Enviar notificación de Filament y correo a cada usuario SSST
        foreach ($usuariosSSST as $usuario) {
            // Notificación en el sistema (Filament)
            Notification::make()
                ->success()
                ->title('Nueva Afiliación Registrada')
                ->body("Se ha registrado una nueva afiliación para **{$afiliacion->nombre_contratista}** (CC: {$afiliacion->numero_documento}). Contrato: {$afiliacion->numero_contrato}.")
                ->icon('heroicon-o-user-plus')
                ->actions([
                    Action::make('ver')
                        ->label('Ver Afiliación')
                        ->url(route('filament.admin.resources.afiliacions.view', ['record' => $afiliacion->id]))
                        ->button(),
                ])
                ->sendToDatabase($usuario);

            // Enviar correo electrónico
            try {
                Mail::to($usuario->email)
                    ->send(new NuevaAfiliacionMail($afiliacion));
            } catch (\Exception $e) {
                // Log del error pero no detener el proceso
                \Log::error('Error al enviar correo de nueva afiliación: ' . $e->getMessage());
            }
        }
    }
}
