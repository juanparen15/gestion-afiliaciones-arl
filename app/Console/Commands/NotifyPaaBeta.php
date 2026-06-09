<?php

namespace App\Console\Commands;

use App\Models\User;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;

class NotifyPaaBeta extends Command
{
    protected $signature = 'paa:notify-beta';

    protected $description = 'Envía a todos los usuarios la notificación del lanzamiento beta del Plan Anual de Adquisiciones';

    public function handle(): int
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->warn('No hay usuarios a quienes notificar.');

            return self::SUCCESS;
        }

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
            ->sendToDatabase($users);

        $this->info("Notificación beta del PAA enviada a {$users->count()} usuarios.");

        return self::SUCCESS;
    }
}
