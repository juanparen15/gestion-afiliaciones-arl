<?php

namespace App\Console\Commands;

use App\Mail\ContratosSecopProximosVencerMail;
use App\Models\Contrato;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotificarContratosSecopProximosAVencer extends Command
{
    protected $signature = 'contratos:notificar-vencimientos {--dias=30 : Días antes del vencimiento para notificar}';

    protected $description = 'Notifica por correo los contratos SECOP próximos a vencer (fecha efectiva con adiciones/prórrogas)';

    public function handle(): int
    {
        $dias = (int) $this->option('dias');

        $this->info("Buscando contratos SECOP que vencen en los próximos {$dias} días...");

        // Traer contratos activos con fecha_terminacion definida
        $contratos = Contrato::with('dependencia')
            ->whereIn('estado', ['EN EJECUCION', 'EN EJECUCION CON ADICION'])
            ->whereNotNull('fecha_terminacion')
            ->get();

        // Filtrar por fecha efectiva de cierre (incluye adiciones y prórrogas)
        $hoy   = now()->startOfDay();
        $limite = now()->addDays($dias)->endOfDay();

        $proximosAVencer = $contratos->filter(function (Contrato $c) use ($hoy, $limite) {
            $cierre = $c->fechaEfectivaCierre();
            return $cierre && $cierre->between($hoy, $limite);
        })->sortBy(fn (Contrato $c) => $c->fechaEfectivaCierre());

        if ($proximosAVencer->isEmpty()) {
            $this->info('No hay contratos SECOP próximos a vencer.');
            return Command::SUCCESS;
        }

        $this->info("Se encontraron {$proximosAVencer->count()} contratos próximos a vencer.");

        $usuariosSSST = User::role('SSST')->get();

        if ($usuariosSSST->isEmpty()) {
            $this->warn('No hay usuarios con rol SSST para notificar.');
        }

        // Agrupar por dependencia
        $porDependencia = $proximosAVencer->groupBy('dependencia_id');

        foreach ($porDependencia as $dependenciaId => $contratosGrupo) {
            $dependencia = $contratosGrupo->first()->dependencia;

            $usuariosDependencia = User::where('dependencia_id', $dependenciaId)
                ->whereNotNull('email')
                ->get();

            $destinatarios = $usuariosDependencia->merge($usuariosSSST)->unique('id');

            if ($destinatarios->isEmpty()) {
                $this->warn("Sin destinatarios para la dependencia: {$dependencia?->nombre}");
                continue;
            }

            $datosCorreo = [
                'dependencia' => $dependencia,
                'contratos'   => $contratosGrupo,
                'diasAlerta'  => $dias,
            ];

            foreach ($destinatarios as $usuario) {
                try {
                    Mail::to($usuario->email)
                        ->send(new ContratosSecopProximosVencerMail($datosCorreo));

                    Notification::make()
                        ->title('Contratos SECOP próximos a vencer')
                        ->body("Hay {$contratosGrupo->count()} contrato(s) de {$dependencia?->nombre} que vencen en los próximos {$dias} días.")
                        ->warning()
                        ->sendToDatabase($usuario);

                    $this->info("Notificación enviada a: {$usuario->email}");
                } catch (\Exception $e) {
                    $this->error("Error enviando a {$usuario->email}: {$e->getMessage()}");
                }
            }
        }

        $this->info('Proceso de notificación de contratos SECOP completado.');

        return Command::SUCCESS;
    }
}
