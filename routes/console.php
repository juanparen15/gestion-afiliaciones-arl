<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Tareas Programadas
|--------------------------------------------------------------------------
|
| Aquí se definen las tareas programadas de la aplicación.
| Para activar el scheduler, agregar al crontab del servidor:
| * * * * * cd /ruta-al-proyecto && php artisan schedule:run >> /dev/null 2>&1
|
*/

// Notificar contratos próximos a vencer - Se ejecuta cada lunes a las 7:00 AM
Schedule::command('afiliaciones:notificar-vencimientos --dias=30')
    ->weeklyOn(1, '07:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->emailOutputOnFailure(env('MAIL_ADMIN_ADDRESS'));

// Actualizar estado de contratos según fecha de cierre efectiva - diario a las 6:00 AM
Schedule::command('contratos:actualizar-estados')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();
