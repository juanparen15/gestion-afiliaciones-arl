<?php

namespace App\Console\Commands;

use App\Models\Planadquisicione;
use App\Models\ProcesoSeleccion;
use Illuminate\Console\Command;

class RevincularPaaProcesos extends Command
{
    protected $signature = 'procesos:revincular-paa {--dry-run : Solo reporta, no escribe}';

    protected $description = 'Re-vincula los procesos de selección a su registro del Plan de Adquisiciones por el consecutivo PAA (vigencia + N° Reg).';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $paa = Planadquisicione::query()
            ->whereNotNull('vigencia')
            ->get(['id', 'id_vigencia', 'vigencia'])
            ->keyBy(fn ($p) => $p->vigencia . '-' . $p->id_vigencia);

        $vinculados = 0; $sinMatch = 0;

        ProcesoSeleccion::whereNotNull('consecutivo_paa')->chunkById(500, function ($procesos) use ($paa, $dryRun, &$vinculados, &$sinMatch) {
            foreach ($procesos as $proc) {
                if (! preg_match('/(\d{4})\D+(\d+)/', $proc->consecutivo_paa, $m)) {
                    $sinMatch++;
                    continue;
                }
                $planId = $paa[$m[1] . '-' . ((int) $m[2])]->id ?? null;
                if (! $planId) {
                    $sinMatch++;
                    continue;
                }
                if ((int) $proc->planadquisicione_id !== (int) $planId) {
                    if (! $dryRun) {
                        $proc->update(['planadquisicione_id' => $planId]);
                    }
                    $vinculados++;
                }
            }
        });

        $verbo = $dryRun ? 'Se vincularían' : 'Vinculados';
        $this->info("{$verbo}: {$vinculados} | Sin coincidencia en el PAA: {$sinMatch}");

        return self::SUCCESS;
    }
}
