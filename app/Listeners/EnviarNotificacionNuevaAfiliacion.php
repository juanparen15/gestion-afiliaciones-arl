<?php

namespace App\Listeners;

use App\Events\AfiliacionCreada;
use App\Mail\NuevaAfiliacionMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

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

        // Enviar correo a cada usuario SSST
        foreach ($usuariosSSST as $usuario) {
            Mail::to($usuario->email)
                ->send(new NuevaAfiliacionMail($event->afiliacion));
        }
    }
}
