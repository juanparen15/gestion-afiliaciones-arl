<?php

namespace App\Console\Commands;

use App\Models\Dependencia;
use App\Models\Elaborador;
use App\Models\Planadquisicione;
use App\Models\ProcesoSeleccion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarProcesosSeleccion extends Command
{
    protected $signature = 'procesos:importar-excel {archivo : Ruta al .xlsx de Consecutivos Procesos Selección}
        {--fresh : Borra los procesos existentes antes de importar}
        {--dry-run : Solo reporta, no escribe}';

    protected $description = 'Importa los procesos de selección (por modalidad) desde el Excel de consecutivos.';

    /** Palabra clave del nombre de hoja → modalidad. */
    private array $modalidadPorHoja = [
        'MINIMA'    => 'MINIMA_CUANTIA',
        'MENOR'     => 'MENOR_CUANTIA',
        'SUBASTA'   => 'SUBASTA_INVERSA',
        'CONCURSO'  => 'CONCURSO_MERITOS',
        'LICITACION' => 'LICITACION_PUBLICA',
    ];

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        if ($this->option('fresh') && ! $dryRun) {
            if ($this->confirm('¿Borrar TODOS los procesos de selección actuales antes de importar?')) {
                DB::table('procesos_seleccion')->delete();
                $this->warn('Procesos borrados.');
            }
        }
        if ($dryRun) {
            $this->warn('MODO DRY-RUN: no se escribe nada.');
        }

        // Índice del PAA (vigencia + N° Reg) → id, para vincular.
        $paa = Planadquisicione::query()
            ->whereNotNull('vigencia')
            ->get(['id', 'id_vigencia', 'vigencia'])
            ->keyBy(fn ($p) => $p->vigencia . '-' . $p->id_vigencia);

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $libro = $reader->load($ruta);

        $creados = 0; $saltados = 0; $sinDep = [];

        foreach ($libro->getSheetNames() as $nombreHoja) {
            $clave = null;
            $hojaUpper = mb_strtoupper($nombreHoja);
            foreach ($this->modalidadPorHoja as $kw => $mod) {
                if (str_contains($hojaUpper, $kw)) { $clave = $mod; break; }
            }
            if (! $clave) {
                continue; // hojas de legenda (Directa, Numeracion) se ignoran
            }

            $hoja = $libro->getSheetByName($nombreHoja);
            $max = $hoja->getHighestDataRow();

            // Fila de encabezado: donde B = "No." o "ITEM".
            $filaEnc = 0;
            for ($r = 1; $r <= min(10, $max); $r++) {
                $b = mb_strtoupper(trim((string) $hoja->getCell('B' . $r)->getValue()));
                if ($b === 'NO.' || $b === 'ITEM') { $filaEnc = $r; break; }
            }
            if (! $filaEnc) {
                continue;
            }

            $this->line("Hoja '{$nombreHoja}' → {$clave}");
            $bar = $this->output->createProgressBar($max - $filaEnc);
            $bar->start();

            for ($i = $filaEnc + 1; $i <= $max; $i++) {
                $bar->advance();

                $objeto = trim((string) $hoja->getCell('D' . $i)->getValue());
                if ($objeto === '' || preg_match('/^\d{1,2}$/', $objeto)) {
                    continue; // vacío o fila-guía (1,2,3...)
                }

                $consecutivo = ltrim(trim((string) $hoja->getCell('B' . $i)->getValue()), "´'");
                $depNombre   = trim((string) $hoja->getCell('E' . $i)->getValue());
                $paaCod      = trim((string) $hoja->getCell('F' . $i)->getValue());
                $elabNombre  = trim((string) $hoja->getCell('G' . $i)->getValue());
                $obs         = trim((string) $hoja->getCell('H' . $i)->getValue());

                if (! $dryRun && $consecutivo !== '' &&
                    ProcesoSeleccion::where('modalidad', $clave)->where('consecutivo', $consecutivo)->exists()) {
                    $saltados++;
                    continue;
                }

                $dep = Dependencia::buscarPorNombre($depNombre);
                if ($depNombre !== '' && ! $dep) {
                    $sinDep[$depNombre] = ($sinDep[$depNombre] ?? 0) + 1;
                }

                if ($dryRun) { $creados++; continue; }

                $fecha = $this->fecha($hoja->getCell('C' . $i)->getValue());
                ProcesoSeleccion::create([
                    'consecutivo'         => $consecutivo ?: null,
                    'vigencia'            => $fecha ? (int) substr($fecha, 0, 4) : (int) date('Y'),
                    'fecha'               => $fecha,
                    'objeto'              => $objeto,
                    'modalidad'           => $clave,
                    'dependencia_id'      => $dep?->id,
                    'dependencia_nombre'  => $depNombre ?: null,
                    'consecutivo_paa'     => $paaCod ?: null,
                    'planadquisicione_id' => $this->resolverPaa($paaCod, $paa),
                    'elaborador_id'       => optional(Elaborador::buscarOCrear($elabNombre))->id,
                    'observaciones'       => $obs ?: null,
                ]);
                $creados++;
            }
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $verbo = $dryRun ? 'Se importarían' : 'Procesos importados';
        $this->info("{$verbo}: {$creados} | Saltados (ya existían): {$saltados}");
        if ($sinDep) {
            $this->warn('Dependencias sin vincular (se guardan como texto):');
            arsort($sinDep);
            foreach ($sinDep as $n => $c) {
                $this->line("  [{$c}] {$n}");
            }
        }

        return self::SUCCESS;
    }

    private function resolverPaa(?string $cod, $indice): ?int
    {
        if (! $cod || ! preg_match('/(\d{4})\D+(\d+)/', $cod, $m)) {
            return null;
        }
        return $indice[$m[1] . '-' . ((int) $m[2])]->id ?? null;
    }

    private function fecha($valor): ?string
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        try {
            if (is_numeric($valor) && $valor > 40000) {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse((string) $valor)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
