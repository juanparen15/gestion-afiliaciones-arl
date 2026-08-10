<?php

namespace App\Console\Commands;

use App\Models\ContratoRegistro;
use App\Models\Dependencia;
use App\Models\Elaborador;
use App\Models\Poliza;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarPolizas extends Command
{
    protected $signature = 'polizas:importar-excel {archivo : Ruta al .xlsx de Aprobación Pólizas}
        {--fresh : Borra las pólizas existentes antes de importar}
        {--dry-run : Solo reporta, no escribe}';

    protected $description = 'Importa la aprobación de pólizas desde el Excel.';

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        if ($this->option('fresh') && ! $dryRun) {
            if ($this->confirm('¿Borrar TODAS las pólizas actuales antes de importar?')) {
                DB::table('polizas')->delete();
                $this->warn('Pólizas borradas.');
            }
        }
        if ($dryRun) {
            $this->warn('MODO DRY-RUN: no se escribe nada.');
        }

        // Índice de contratos por "AÑO-N°" para vincular.
        $contratos = ContratoRegistro::whereNotNull('fecha')->get(['id', 'numero', 'fecha'])
            ->keyBy(fn ($c) => $c->fecha->format('Y') . '-' . ltrim((string) $c->numero, '0'));

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $hoja = $reader->load($ruta)->getSheet(0);
        $max = $hoja->getHighestDataRow();

        // Fila de encabezado: donde D contiene CONTRATO.
        $filaEnc = 0;
        for ($r = 1; $r <= min(10, $max); $r++) {
            if (str_contains(mb_strtoupper((string) $hoja->getCell('D' . $r)->getValue()), 'CONTRATO')) {
                $filaEnc = $r; break;
            }
        }
        if (! $filaEnc) {
            $this->error('No se encontró la fila de encabezados (columna D = CONTRATO).');
            return self::FAILURE;
        }

        $creados = 0; $saltados = 0; $vinculados = 0; $sinDep = [];
        $bar = $this->output->createProgressBar($max - $filaEnc);
        $bar->start();

        for ($i = $filaEnc + 1; $i <= $max; $i++) {
            $bar->advance();

            $contrato = trim((string) $hoja->getCell('D' . $i)->getValue());
            $consecutivo = trim((string) $hoja->getCell('B' . $i)->getValue());
            if ($contrato === '' && $consecutivo === '') {
                continue;
            }

            if (! $dryRun && $consecutivo !== '' &&
                Poliza::where('consecutivo', $consecutivo)->where('contrato_texto', $contrato ?: null)->exists()) {
                $saltados++;
                continue;
            }

            $depNombre = trim((string) $hoja->getCell('F' . $i)->getValue());
            $dep = $depNombre !== '' ? Dependencia::buscarPorNombre($depNombre) : null;
            if ($depNombre !== '' && ! $dep) {
                $sinDep[$depNombre] = ($sinDep[$depNombre] ?? 0) + 1;
            }

            $contratoId = null;
            if ($contrato !== '' && preg_match('/(\d+)\D+(\d{4})/', $contrato, $m)) {
                $contratoId = $contratos[$m[2] . '-' . ltrim($m[1], '0')]->id ?? null;
                if ($contratoId) {
                    $vinculados++;
                }
            }

            if ($dryRun) { $creados++; continue; }

            Poliza::create([
                'consecutivo'          => $consecutivo ?: null,
                'fecha'                => $this->fecha($hoja->getCell('C' . $i)->getValue()),
                'contrato_texto'       => $contrato ?: null,
                'contrato_registro_id' => $contratoId,
                'estado'               => ($e = mb_strtoupper(trim((string) $hoja->getCell('E' . $i)->getValue()))) !== '' ? $e : null,
                'dependencia_id'       => $dep?->id,
                'dependencia_nombre'   => $depNombre ?: null,
                'aprobador_id'         => optional(Elaborador::buscarOCrear(trim((string) $hoja->getCell('G' . $i)->getValue())))->id,
                'observaciones'        => trim((string) $hoja->getCell('H' . $i)->getValue()) ?: null,
            ]);
            $creados++;
        }
        $bar->finish();
        $this->newLine(2);

        $verbo = $dryRun ? 'Se importarían' : 'Pólizas importadas';
        $this->info("{$verbo}: {$creados} | Saltadas (ya existían): {$saltados} | Vinculadas a contrato: {$vinculados}");
        if ($sinDep) {
            $this->warn('Dependencias sin vincular (se guardan como texto):');
            arsort($sinDep);
            foreach ($sinDep as $n => $c) {
                $this->line("  [{$c}] {$n}");
            }
        }

        return self::SUCCESS;
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
