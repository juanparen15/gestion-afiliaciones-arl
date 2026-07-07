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
    protected $signature = 'actas:importar-excel {archivo : Ruta al .xlsx de respuestas}';

    protected $description = 'Importa las actas de necesidad existentes desde el Excel de respuestas (columna Q = consecutivo).';

    public function handle(): int
    {
        $ruta = $this->argument('archivo');
        if (! is_file($ruta)) {
            $this->error("No existe el archivo: {$ruta}");
            return self::FAILURE;
        }

        $reader = IOFactory::createReaderForFile($ruta);
        $reader->setReadDataOnly(true);
        $sheet = $reader->load($ruta)->getSheet(0);
        $max = $sheet->getHighestRow();

        // Cache de dependencias/áreas por nombre normalizado
        $deps  = Dependencia::all()->keyBy(fn($d) => $this->norm($d->nombre));
        $areas = Area::all()->keyBy(fn($a) => $this->norm($a->nombre));

        $creadas = 0; $saltadas = 0;
        $bar = $this->output->createProgressBar($max - 1);
        $bar->start();

        for ($i = 2; $i <= $max; $i++) {
            $bar->advance();
            $codigo = trim((string) $sheet->getCell('Q' . $i)->getValue());
            if ($codigo === '' || ! is_numeric($codigo)) {
                continue;
            }
            $consecutivo = (int) $codigo;

            if (ActaNecesidad::where('consecutivo', $consecutivo)->exists()) {
                $saltadas++;
                continue;
            }

            $depNombre  = trim((string) $sheet->getCell('C' . $i)->getValue());
            $areaNombre = trim((string) $sheet->getCell('D' . $i)->getValue());

            ActaNecesidad::create([
                'consecutivo'              => $consecutivo,
                'email_solicitante'        => trim((string) $sheet->getCell('B' . $i)->getValue()) ?: null,
                'dependencia_id'           => $deps[$this->norm($depNombre)]->id ?? null,
                'area_id'                  => $areas[$this->norm($areaNombre)]->id ?? null,
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
        $this->info("Actas importadas: {$creadas} | Saltadas (ya existían): {$saltadas}");
        $this->info('Próximo consecutivo: ' . ActaNecesidad::siguienteConsecutivo());

        return self::SUCCESS;
    }

    private function norm(?string $s): string
    {
        $s = mb_strtoupper(trim((string) $s));
        return preg_replace('/\s+/', ' ', $s);
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
