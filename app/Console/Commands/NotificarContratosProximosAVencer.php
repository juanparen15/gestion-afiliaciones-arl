<?php

namespace App\Console\Commands;

use App\Mail\ContratosProximosVencerMail;
use App\Models\Afiliacion;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarContratosProximosAVencer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'afiliaciones:notificar-vencimientos {--dias=30 : Días antes del vencimiento para notificar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notifica por correo los contratos próximos a vencer a los usuarios de la dependencia y usuarios SST';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dias = (int) $this->option('dias');

        $this->info("Buscando contratos que vencen en los próximos {$dias} días...");

        // Obtener afiliaciones próximas a vencer
        $afiliacionesProximasAVencer = Afiliacion::with(['dependencia', 'area'])
            ->where('estado', 'validado')
            ->where(function ($query) use ($dias) {
                // Verificar fecha_fin normal o fecha de prórroga si aplica
                $query->where(function ($q) use ($dias) {
                    $q->where('tiene_prorroga', false)
                        ->where('fecha_fin', '>=', now())
                        ->where('fecha_fin', '<=', now()->addDays($dias));
                })->orWhere(function ($q) use ($dias) {
                    $q->where('tiene_prorroga', true)
                        ->where('nueva_fecha_fin_prorroga', '>=', now())
                        ->where('nueva_fecha_fin_prorroga', '<=', now()->addDays($dias));
                });
            })
            ->where(function ($query) {
                // Excluir contratos con terminación anticipada
                $query->where('tiene_terminacion_anticipada', false)
                    ->orWhereNull('tiene_terminacion_anticipada');
            })
            ->orderBy('fecha_fin')
            ->get();

        if ($afiliacionesProximasAVencer->isEmpty()) {
            $this->info('No hay contratos próximos a vencer.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$afiliacionesProximasAVencer->count()} contratos próximos a vencer.");

        // Obtener usuarios SSST
        $usuariosSSST = User::role('SSST')->get();

        if ($usuariosSSST->isEmpty()) {
            $this->warn('No hay usuarios con rol SSST para notificar.');
        }

        // Agrupar afiliaciones por dependencia
        $afiliacionesPorDependencia = $afiliacionesProximasAVencer->groupBy('dependencia_id');

        foreach ($afiliacionesPorDependencia as $dependenciaId => $afiliaciones) {
            $dependencia = $afiliaciones->first()->dependencia;

            // Obtener usuarios de la dependencia
            $usuariosDependencia = User::where('dependencia_id', $dependenciaId)
                ->whereNotNull('email')
                ->get();

            // Combinar destinatarios: usuarios de la dependencia + usuarios SSST
            $destinatarios = $usuariosDependencia->merge($usuariosSSST)->unique('id');

            if ($destinatarios->isEmpty()) {
                $this->warn("No hay destinatarios para la dependencia: {$dependencia?->nombre}");
                continue;
            }

            // Preparar datos para el correo
            $datosCorreo = [
                'dependencia' => $dependencia,
                'afiliaciones' => $afiliaciones,
                'diasAlerta' => $dias,
            ];

            // Enviar correo a cada destinatario
            foreach ($destinatarios as $usuario) {
                try {
                    Mail::to($usuario->email)
                        ->send(new ContratosProximosVencerMail($datosCorreo));

                    // Enviar notificación en el panel de Filament
                    Notification::make()
                        ->title('Contratos próximos a vencer')
                        ->body("Hay {$afiliaciones->count()} contrato(s) de {$dependencia?->nombre} próximos a vencer en los próximos {$dias} días.")
                        ->warning()
                        ->sendToDatabase($usuario);

                    $this->info("Notificación enviada a: {$usuario->email}");
                } catch (\Exception $e) {
                    $this->error("Error enviando a {$usuario->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Proceso de notificación completado.');

        return Command::SUCCESS;
    }
}
