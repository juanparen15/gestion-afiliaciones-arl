<?php

namespace App\Console\Commands;

use App\Models\ContratoRegistro;
use App\Models\Dependencia;
use App\Models\Elaborador;
use App\Models\Planadquisicione;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarContratosRegistro extends Command
{
    protected $signature = 'contratos:importar-excel {archivo : Ruta al .xlsx de Consecutivos Contratos y demás}
        {--fresh : Borra los registros existentes antes de importar}
        {--dry-run : Solo reporta, no escribe}';

    protected $description = 'Importa contratos, convenios y comodatos desde el Excel de consecutivos.';

    /** Palabra clave de hoja → [tipo, mapa de columnas]. */
    private array $hojas = [
        'CONTRATOS' => ['tipo' => 'CONTRATO', 'cols' => [
            'numero' => 'B', 'fecha' => 'C', 'contratista' => 'D', 'proceso_texto' => 'E',
            'consecutivo_paa' => 'F', 'modalidad' => 'G', 'dependencia' => 'H', 'valor' => 'I',
            'elaborador' => 'J', 'obs' => 'K',
        ]],
        'CONVENIOS' => ['tipo' => 'CONVENIO', 'cols' => [
            'numero' => 'B', 'fecha' => 'C', 'contratista' => 'D', 'proceso_texto' => 'E',
            'consecutivo_paa' => 'F', 'valor' => 'G', 'elaborador' => 'H', 'obs' => 'I',
        ]],
        'COMODATOS' => ['tipo' => 'COMODATO', 'cols' => [
            'numero' => 'B', 'fecha' => 'C', 'contratista' => 'D', 'proceso_texto' => 'E',
            'elaborador' => 'F', 'obs' => 'G',
        ]],
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
            if ($this->confirm('¿Borrar TODOS los contratos/convenios/comodatos actuales antes de importar?')) {
                DB::table('contrato_registros')->delete();
                $this->warn('Registros borrados.');
            }
        }
        if ($dryRun) {
            $this->warn('MODO DRY-RUN: no se escribe nada.');
        }

        $paa = Planadquisicione::query()->whereNotNull('vigencia')
            ->get(['id', 'id_vigencia', 'vigencia'])
            ->keyBy(fn ($p) => $p->vigencia . '-' . $p->id_vigencia);

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $libro = $reader->load($ruta);

        $creados = 0; $saltados = 0; $sinDep = [];

        foreach ($libro->getSheetNames() as $nombreHoja) {
            $cfg = null;
            $up = mb_strtoupper($nombreHoja);
            foreach ($this->hojas as $kw => $c) {
                if (str_contains($up, $kw)) { $cfg = $c; break; }
            }
            if (! $cfg) {
                continue;
            }

            $hoja = $libro->getSheetByName($nombreHoja);
            $max = $hoja->getHighestDataRow();

            // Fila de encabezado: donde D contiene CONTRATISTA.
            $filaEnc = 0;
            for ($r = 1; $r <= min(10, $max); $r++) {
                if (str_contains(mb_strtoupper((string) $hoja->getCell('D' . $r)->getValue()), 'CONTRATISTA')) {
                    $filaEnc = $r; break;
                }
            }
            if (! $filaEnc) {
                continue;
            }

            $this->line("Hoja '{$nombreHoja}' → {$cfg['tipo']}");
            $cols = $cfg['cols'];
            $bar = $this->output->createProgressBar($max - $filaEnc);
            $bar->start();

            for ($i = $filaEnc + 1; $i <= $max; $i++) {
                $bar->advance();
                $get = fn (string $campo) => isset($cols[$campo])
                    ? trim((string) $hoja->getCell($cols[$campo] . $i)->getValue()) : '';

                $contratista = $get('contratista');
                if ($contratista === '' || preg_match('/^\d{1,2}$/', $contratista)) {
                    continue;
                }

                $numero = ltrim($get('numero'), "´'");
                $depNombre = $get('dependencia');
                $paaCod = $get('consecutivo_paa');

                if (! $dryRun && $numero !== '' &&
                    ContratoRegistro::where('tipo', $cfg['tipo'])->where('numero', $numero)->exists()) {
                    $saltados++;
                    continue;
                }

                $dep = $depNombre !== '' ? Dependencia::buscarPorNombre($depNombre) : null;
                if ($depNombre !== '' && ! $dep) {
                    $sinDep[$depNombre] = ($sinDep[$depNombre] ?? 0) + 1;
                }

                if ($dryRun) { $creados++; continue; }

                ContratoRegistro::create([
                    'tipo'                => $cfg['tipo'],
                    'numero'              => $numero ?: null,
                    'fecha'               => $this->fecha($hoja->getCell($cols['fecha'] . $i)->getValue()),
                    'contratista'         => $contratista,
                    'proceso_texto'       => $get('proceso_texto') ?: null,
                    'modalidad'           => $get('modalidad') ?: null,
                    'dependencia_id'      => $dep?->id,
                    'dependencia_nombre'  => $depNombre ?: null,
                    'consecutivo_paa'     => $paaCod ?: null,
                    'planadquisicione_id' => $this->resolverPaa($paaCod, $paa),
                    'elaborador_id'       => optional(Elaborador::buscarOCrear($get('elaborador')))->id,
                    'valor'               => $this->numero($get('valor')),
                    'observaciones'       => $get('obs') ?: null,
                ]);
                $creados++;
            }
            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $verbo = $dryRun ? 'Se importarían' : 'Registros importados';
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

    private function numero($valor): ?float
    {
        if ($valor === null || $valor === '') {
            return null;
        }
        if (is_numeric($valor)) {
            return (float) $valor;
        }
        $limpio = preg_replace('/[^\d]/', '', (string) $valor);
        return $limpio !== '' ? (float) $limpio : null;
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
