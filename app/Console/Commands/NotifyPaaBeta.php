<?php

namespace App\Console\Commands;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotifyPaaBeta extends Command
{
    protected $signature = 'paa:notify-beta';

    protected $description = 'Envía a todos los usuarios la notificación del lanzamiento beta del Plan Anual de Adquisiciones';

    public function handle(): int
    {
        $usuarios = User::all();

        if ($usuarios->isEmpty()) {
            $this->warn('No hay usuarios a quienes notificar.');

            return self::SUCCESS;
        }

        // Construimos la notificación Filament (con botón) y la persistimos
        // con inserción directa en la tabla notifications, que es el patrón
        // que funciona de forma fiable con la campanita de Filament.
        $data = json_encode(
            Notification::make()
                ->title('Nuevo módulo: Plan Anual de Adquisiciones (Beta)')
                ->icon('heroicon-o-document-text')
                ->iconColor('info')
                ->body('Se está integrando el registro del Plan Anual de Adquisiciones (PAA) en el sistema de Afiliaciones ARL. Esta es una fase beta para la Alcaldía de Puerto Boyacá. Ya puedes registrar tus planes desde el menú "Plan de Adquisiciones".')
                ->actions([
                    Action::make('ver')
                        ->label('Ir a Plan de Adquisiciones')
                        ->url(route('filament.admin.resources.planadquisiciones.index'))
                        ->button()
                        ->markAsRead(),
                ])
                ->getDatabaseMessage()
        );

        $ahora = now()->toDateTimeString();

        $records = $usuarios->map(fn ($user) => [
            'id' => (string) Str::orderedUuid(),
            'type' => \Filament\Notifications\Notification::class,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => null,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ])->values()->all();

        DB::table('notifications')->insert($records);

        $this->info("Notificación beta del PAA enviada a {$usuarios->count()} usuarios.");

        return self::SUCCESS;
    }
}
