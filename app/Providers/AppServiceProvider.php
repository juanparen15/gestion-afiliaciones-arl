<?php

namespace App\Providers;

use App\Events\AfiliacionCreada;
use App\Listeners\EnviarNotificacionNuevaAfiliacion;
use App\Models\Afiliacion;
use App\Observers\AfiliacionObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Registrar observer para afiliaciones
        Afiliacion::observe(AfiliacionObserver::class);

        // Registrar listener para enviar notificación cuando se crea una nueva afiliación
        Event::listen(
            AfiliacionCreada::class,
            EnviarNotificacionNuevaAfiliacion::class
        );
    }
}
