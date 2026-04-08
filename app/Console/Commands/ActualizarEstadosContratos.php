<?php

namespace App\Console\Commands;

use App\Models\Contrato;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ActualizarEstadosContratos extends Command
{
    protected $signature   = 'contratos:actualizar-estados';
    protected $description = 'Actualiza el estado de los contratos según su fecha de cierre efectiva.';

    public function handle(): int
    {
        $hoy = Carbon::today();

        // Solo contratos en ejecución (activos o con adición)
        $contratos = Contrato::whereIn('estado', [
            'EN EJECUCION',
            'EN EJECUCION CON ADICION',
        ])->whereNotNull('fecha_terminacion')->get();

        $actualizados = 0;

        foreach ($contratos as $contrato) {
            $cierreEfectivo = $contrato->fechaEfectivaCierre();

            if ($cierreEfectivo === null) {
                continue;
            }

            if ($cierreEfectivo->lt($hoy)) {
                // El plazo ya venció → TERMINADO
                $nuevoEstado = 'TERMINADO';
            } elseif ($contrato->tieneAdiciones()) {
                // Aún vigente y tiene adiciones → EN EJECUCION CON ADICION
                $nuevoEstado = 'EN EJECUCION CON ADICION';
            } else {
                // Aún vigente sin adiciones → EN EJECUCION
                $nuevoEstado = 'EN EJECUCION';
            }

            if ($contrato->estado !== $nuevoEstado) {
                $estadoAnterior = $contrato->estado;
                $contrato->update(['estado' => $nuevoEstado]);
                $actualizados++;
                $this->line("  [{$contrato->numero_contrato}] {$estadoAnterior} → {$nuevoEstado}");
            }
        }

        $this->info("Contratos revisados: {$contratos->count()} | Actualizados: {$actualizados}");

        return self::SUCCESS;
    }
}
