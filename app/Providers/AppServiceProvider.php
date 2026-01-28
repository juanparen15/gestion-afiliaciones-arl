<?php

namespace App\Providers;

use App\Events\AfiliacionCreada;
use App\Listeners\EnviarNotificacionNuevaAfiliacion;
use App\Models\Afiliacion;
use App\Observers\AfiliacionObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
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
        // Forzar HTTPS y URL base solo en producción
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Registrar observer para afiliaciones
        Afiliacion::observe(AfiliacionObserver::class);

        // Registrar listener para enviar notificación cuando se crea una nueva afiliación
        Event::listen(
            AfiliacionCreada::class,
            EnviarNotificacionNuevaAfiliacion::class
        );
    }
}
