<?php

namespace App\Console\Commands;

use App\Models\ActaNecesidad;
use App\Models\Area;
use App\Models\Dependencia;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportarActasNecesidad extends Command
{
    protected $signature = 'actas:importar-excel {archivo : Ruta al .xlsx de respuestas}
        {--fresh : Borra DEFINITIVAMENTE todas las actas actuales antes de importar}
        {--force : No pedir confirmación para --fresh}
        {--dry-run : Solo reporta lo que se importaría (no borra ni escribe nada)}';

    protected $description = 'Importa las actas de necesidad existentes desde el Excel de respuestas (columna Q = consecutivo).';

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $dryRun = $this->option('dry-run');

        if ($this->option('fresh') && ! $dryRun) {
            $total = ActaNecesidad::withTrashed()->count();
            if (! $this->option('force')
                && ! $this->confirm("Se BORRARÁN definitivamente {$total} actas actuales antes de importar. ¿Continuar?")) {
                $this->warn('Operación cancelada.');
                return self::FAILURE;
            }
            // Borrado físico (bypass soft-delete) para no saltar consecutivos al reimportar.
            \Illuminate\Support\Facades\DB::table('actas_necesidad')->delete();
            $this->warn("Actas borradas: {$total}.");
        }

        if ($dryRun) {
            $this->warn('MODO DRY-RUN: no se borra ni escribe nada.');
        }

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($ruta)->getSheet(0);
        $max = $sheet->getHighestRow();

        // Cache de dependencias/áreas por nombre normalizado
        $deps  = Dependencia::all()->keyBy(fn($d) => $this->norm($d->nombre));
        $areas = Area::all()->keyBy(fn($a) => $this->norm($a->nombre));

        $creadas = 0; $saltadas = 0; $sinCodigo = 0;
        $depSinVincular = []; $areaSinVincular = [];
        $bar = $this->output->createProgressBar($max - 1);
        $bar->start();

        for ($i = 2; $i <= $max; $i++) {
            $bar->advance();
            $codigo = trim((string) $sheet->getCell('Q' . $i)->getValue());
            if ($codigo === '' || ! is_numeric($codigo)) {
                $sinCodigo++;
                continue;
            }
            $consecutivo = (int) $codigo;

            // En dry-run se evalúan todas las filas (simula una importación fresca).
            if (! $dryRun && ActaNecesidad::where('consecutivo', $consecutivo)->exists()) {
                $saltadas++;
                continue;
            }

            $depNombre  = trim((string) $sheet->getCell('C' . $i)->getValue());
            $areaNombre = trim((string) $sheet->getCell('D' . $i)->getValue());

            $depNorm  = $this->norm($depNombre);
            $areaNorm = $this->norm($areaNombre);
            $depKey   = $this->aliasDep()[$depNorm] ?? $depNorm;
            $areaKey  = $this->aliasArea()[$areaNorm] ?? $areaNorm;
            $depId    = $deps[$depKey]->id ?? null;
            $areaId   = $areas[$areaKey]->id ?? null;
            if ($depNombre !== '' && $depId === null)  $depSinVincular[$depNombre]  = ($depSinVincular[$depNombre] ?? 0) + 1;
            if ($areaNombre !== '' && $areaId === null) $areaSinVincular[$areaNombre] = ($areaSinVincular[$areaNombre] ?? 0) + 1;

            if ($dryRun) {
                $creadas++;
                continue;
            }

            ActaNecesidad::create([
                'consecutivo'              => $consecutivo,
                'email_solicitante'        => trim((string) $sheet->getCell('B' . $i)->getValue()) ?: null,
                'dependencia_id'           => $depId,
                'area_id'                  => $areaId,
                'dependencia_nombre'       => $depNombre ?: null,
                'area_nombre'              => $areaNombre ?: null,
                'nombre_solicitante'       => trim((string) $sheet->getCell('E' . $i)->getValue()) ?: null,
                'objeto_contrato'          => trim((string) $sheet->getCell('F' . $i)->getValue()) ?: null,
                'tipo_contrato'            => trim((string) $sheet->getCell('G' . $i)->getValue()) ?: null,
                'duracion'                 => trim((string) $sheet->getCell('H' . $i)->getValue()) ?: null,
                'modalidad_seleccion'      => trim((string) $sheet->getCell('I' . $i)->getValue()) ?: null,
                'tipo_solicitud'           => trim((string) $sheet->getCell('J' . $i)->getValue()) ?: null,
                'numero_contrato_convenio' => trim((string) $sheet->getCell('K' . $i)->getValue()) ?: null,
                'presupuesto_oficial'      => $this->numero($sheet->getCell('L' . $i)->getValue()),
                'codigo_bpim_bpin'         => trim((string) $sheet->getCell('M' . $i)->getValue()) ?: null,
                'codigo_paa'               => trim((string) $sheet->getCell('N' . $i)->getValue()) ?: null,
                'observaciones'            => trim((string) $sheet->getCell('O' . $i)->getValue()) ?: null,
                'nombre_completo'          => trim((string) $sheet->getCell('P' . $i)->getValue()) ?: null,
                'estado'                   => 'aprobado',
                'fecha_solicitud'          => $this->fecha($sheet->getCell('A' . $i)->getValue()),
                'fecha_aprobado'           => $this->fecha($sheet->getCell('A' . $i)->getValue()),
            ]);
            $creadas++;
        }

        $bar->finish();
        $this->newLine(2);

        $verbo = $dryRun ? 'Se importarían' : 'Actas importadas';
        $this->info("{$verbo}: {$creadas} | Saltadas (ya existían): {$saltadas} | Sin consecutivo (se omiten): {$sinCodigo}");

        if ($depSinVincular) {
            $this->newLine();
            $this->warn('Dependencias del Excel SIN vincular a FK (se guardan como texto, se ven bien igual):');
            arsort($depSinVincular);
            foreach ($depSinVincular as $n => $c) {
                $this->line("  [{$c}] {$n}");
            }
        }
        if ($areaSinVincular) {
            $this->newLine();
            $this->warn('Áreas del Excel SIN vincular a FK (' . count($areaSinVincular) . ' distintas, se guardan como texto):');
            arsort($areaSinVincular);
            $k = 0;
            foreach ($areaSinVincular as $n => $c) {
                $this->line("  [{$c}] {$n}");
                if (++$k >= 30) {
                    $this->line('  ...(' . (count($areaSinVincular) - 30) . ' más)');
                    break;
                }
            }
        }

        $this->newLine();
        $this->info('Próximo consecutivo: ' . ActaNecesidad::siguienteConsecutivo());

        return self::SUCCESS;
    }

    private function norm(?string $s): string
    {
        // Sin acentos, mayúsculas y espacios colapsados para comparar nombres.
        $s = \Illuminate\Support\Str::ascii((string) $s);
        $s = mb_strtoupper(trim($s));
        return preg_replace('/\s+/', ' ', $s);
    }

    /** Alias nombre-corto (Excel) → nombre oficial (BD) para dependencias. Claves y valores normalizados. */
    private function aliasDep(): array
    {
        return [
            'SECRETARIA DE DESARROLLO'           => 'SECRETARIA DE DESARROLLO SOCIAL Y COMUNITARIO',
            'SECRETARIA DE GOBIERNO'             => 'SECRETARIA DE GOBIERNO MUNICIPAL Y CONVIVENCIA CIUDADANA',
            'SECRETARIA DE GENERAL'              => 'SECRETARIA GENERAL Y DE SERVICIOS ADMINISTRATIVOS',
            'SECRETARIA GENERAL'                 => 'SECRETARIA GENERAL Y DE SERVICIOS ADMINISTRATIVOS',
            'SECRETARIA DE PLANEACION'           => 'SECRETARIA DE PLANEACION MUNICIPAL',
            'DIRECCION DE TRANSITO Y TRANSPORTE' => 'INSPECCION DE TRANSITO Y TRANSPORTE',
            'UMATA'                              => 'UNIDAD DE ASISTENCIA TECNICA -UMATA',
            'DESPACHO ALCALDE'                   => 'DESPACHO',
        ];
    }

    /** Alias (Excel) → nombre oficial (BD) para áreas. Solo los inequívocos; el resto queda como texto. */
    private function aliasArea(): array
    {
        return [
            'SISTEMAS'                                    => 'AREA DE SISTEMAS',
            'BANCO PROYECTOS'                             => 'BANCO DE PROYECTOS',
            'ARCHIVO'                                     => 'ARCHIVO CENTRAL',
            'PERSONAL'                                    => 'AREA DE PERSONAL',
            'ALMACEN'                                     => 'ALMACEN MUNICIPAL',
            'INSTITUTO MUNICIPAL DE DEPORTE Y RECREACION' => 'IMDR',
            'SALUD PUBLICA'                               => 'SALUD',
            'DESARROLLO SOCIAL - AREA DE SALUD PUBLICA'   => 'SALUD',
            'SECRETARIA DE DESARROLLO SOCIAL - AREA DE SISBEN' => 'SISBEN',
            'OFICINA DE SISTEMAS'                         => 'AREA DE SISTEMAS',
            'CULTURA'                                     => 'CASA DE LA CULTURA - GUILLERMO CANO ISAZA',
        ];
    }

    private function numero($valor): ?float
    {
        if ($valor === null || $valor === '') return null;
        if (is_numeric($valor)) return (float) $valor;
        $limpio = preg_replace('/[^\d]/', '', (string) $valor);
        return $limpio !== '' ? (float) $limpio : null;
    }

    private function fecha($valor): ?string
    {
        if ($valor === null || $valor === '') return null;
        try {
            if (is_numeric($valor)) {
                return ExcelDate::excelToDateTimeObject((float) $valor)->format('Y-m-d H:i:s');
            }
            return \Carbon\Carbon::parse((string) $valor)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
