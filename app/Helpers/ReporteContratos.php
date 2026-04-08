<?php

namespace App\Helpers;

use App\Models\Contrato;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ReporteContratos
{
    // Colores institucionales
    const COLOR_HEADER_BG  = 'FF0F2040';   // navy
    const COLOR_TITLE_BG   = 'FF0369A1';   // sky-700
    const COLOR_TOTAL_BG   = 'FFE0F2FE';   // sky-100
    const COLOR_ALT_ROW    = 'FFF8FAFC';   // slate-50
    const COLOR_WHITE      = 'FFFFFFFF';
    const COLOR_BORDER     = 'FFCBD5E1';   // slate-300
    const FORMAT_CURRENCY  = '$ #,##0';
    const FORMAT_INTEGER   = '#,##0';

    /**
     * Genera el Excel y retorna la ruta al archivo temporal.
     */
    public static function generar(string $vigencia, string $periodo, array $incluir): string
    {
        $contratos = Contrato::where('vigencia', $vigencia)
            ->orderBy('fecha_inicio')
            ->get();

        // ── Etiquetas por período ──
        $periodos = match ($periodo) {
            'mensual'    => [
                1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril',
                5=>'Mayo',  6=>'Junio',   7=>'Julio', 8=>'Agosto',
                9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre',
            ],
            'trimestral' => [1=>'T1 (Ene–Mar)', 2=>'T2 (Abr–Jun)', 3=>'T3 (Jul–Sep)', 4=>'T4 (Oct–Dic)'],
            'semestral'  => [1=>'S1 (Ene–Jun)', 2=>'S2 (Jul–Dic)'],
            'anual'      => [1 => "Anual {$vigencia}"],
        };

        // ── Mapeo fecha → clave de período ──
        $getKey = static function ($fecha) use ($periodo): ?int {
            if (!$fecha) return null;
            $mes = (int) $fecha->format('n');
            return match ($periodo) {
                'mensual'    => $mes,
                'trimestral' => (int) ceil($mes / 3),
                'semestral'  => $mes <= 6 ? 1 : 2,
                'anual'      => 1,
            };
        };

        // ── Agregación ──
        $agg = [];
        foreach ($contratos as $c) {
            $key = $getKey($c->fecha_inicio);
            if ($key === null) continue;

            $agg[$key] ??= ['cant' => 0, 'valor' => 0.0, 'num_adic' => 0, 'val_adic' => 0.0];
            $agg[$key]['cant']++;
            $agg[$key]['valor'] += (float) ($c->valor_contrato ?? 0);

            $va = (float) ($c->valor_adicional_1 ?? 0)
                + (float) ($c->valor_adicional_2 ?? 0)
                + (float) ($c->valor_adicional_3 ?? 0);
            if ($va > 0 || $c->fecha_adicional_1) {
                $agg[$key]['num_adic']++;
                $agg[$key]['val_adic'] += $va;
            }
        }

        // ── Spreadsheet ──
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setTitle("Reporte Contratos {$vigencia}")
            ->setCreator('Sistema ARL – Alcaldía de Puerto Boyacá');

        // ════════════════════════════════════════
        // HOJA 1 — RESUMEN POR PERÍODO
        // ════════════════════════════════════════
        $ws = $spreadsheet->getActiveSheet()->setTitle('Resumen');

        $encabezados = ['Período'];
        if (in_array('cantidades',  $incluir)) $encabezados[] = 'N° Contratos';
        if (in_array('valores',     $incluir)) $encabezados[] = 'Valor Total ($)';
        if (in_array('adicionales', $incluir)) {
            $encabezados[] = 'N° Contratos con Adición';
            $encabezados[] = 'Valor Adiciones ($)';
        }

        $numCols    = count($encabezados);
        $lastLetter = Coordinate::stringFromColumnIndex($numCols);

        // Fila 1 – Título
        $tituloTexto = "Reporte de Contratos — Vigencia {$vigencia} — "
            . match ($periodo) {
                'mensual'    => 'Mensual',
                'trimestral' => 'Trimestral',
                'semestral'  => 'Semestral',
                'anual'      => 'Anual',
            };

        $ws->mergeCells("A1:{$lastLetter}1");
        $ws->setCellValue('A1', $tituloTexto);
        $ws->getRowDimension(1)->setRowHeight(30);
        $ws->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => self::COLOR_WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TITLE_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Fila 2 – Encabezados de columnas
        foreach ($encabezados as $i => $h) {
            $ws->setCellValueByColumnAndRow($i + 1, 2, $h);
        }
        $ws->getStyle("A2:{$lastLetter}2")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_WHITE]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::COLOR_HEADER_BG]]],
        ]);
        $ws->getRowDimension(2)->setRowHeight(22);

        // Filas de datos
        $row     = 3;
        $totales = ['cant' => 0, 'valor' => 0.0, 'num_adic' => 0, 'val_adic' => 0.0];

        // Solo mostrar períodos que tienen al menos un contrato
        $periodosConDatos = array_filter($periodos, fn($key) => isset($agg[$key]), ARRAY_FILTER_USE_KEY);

        foreach ($periodosConDatos as $key => $label) {
            $d   = $agg[$key];
            $col = 1;

            $ws->setCellValueByColumnAndRow($col++, $row, $label);

            if (in_array('cantidades', $incluir)) {
                $ws->setCellValueByColumnAndRow($col++, $row, $d['cant']);
                $totales['cant'] += $d['cant'];
            }
            if (in_array('valores', $incluir)) {
                $ws->setCellValueByColumnAndRow($col++, $row, $d['valor']);
                $totales['valor'] += $d['valor'];
            }
            if (in_array('adicionales', $incluir)) {
                $ws->setCellValueByColumnAndRow($col++, $row, $d['num_adic']);
                $ws->setCellValueByColumnAndRow($col++, $row, $d['val_adic']);
                $totales['num_adic'] += $d['num_adic'];
                $totales['val_adic'] += $d['val_adic'];
            }

            // Fila alterna
            if ($row % 2 === 0) {
                $ws->getStyle("A{$row}:{$lastLetter}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ALT_ROW]],
                ]);
            }

            // Bordes
            $ws->getStyle("A{$row}:{$lastLetter}{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_HAIR, 'color' => ['argb' => self::COLOR_BORDER]]],
            ]);

            $row++;
        }

        // Fila de totales
        $col = 1;
        $ws->setCellValueByColumnAndRow($col++, $row, 'TOTAL');
        if (in_array('cantidades',  $incluir)) $ws->setCellValueByColumnAndRow($col++, $row, $totales['cant']);
        if (in_array('valores',     $incluir)) $ws->setCellValueByColumnAndRow($col++, $row, $totales['valor']);
        if (in_array('adicionales', $incluir)) {
            $ws->setCellValueByColumnAndRow($col++, $row, $totales['num_adic']);
            $ws->setCellValueByColumnAndRow($col++, $row, $totales['val_adic']);
        }
        $ws->getStyle("A{$row}:{$lastLetter}{$row}")->applyFromArray([
            'font'    => ['bold' => true],
            'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_TOTAL_BG]],
            'borders' => [
                'top'    => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLOR_TITLE_BG]],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => self::COLOR_TITLE_BG]],
            ],
        ]);

        // Formatos numéricos
        self::aplicarFormatosResumen($ws, $encabezados, $row);

        // Auto-ancho
        foreach (range(1, $numCols) as $ci) {
            $ws->getColumnDimensionByColumn($ci)->setAutoSize(true);
        }

        // ════════════════════════════════════════
        // HOJA 2 — DETALLE DE CONTRATOS (opcional)
        // ════════════════════════════════════════
        if (in_array('contratos', $incluir)) {
            $ws2 = $spreadsheet->createSheet()->setTitle('Contratos');

            $hd2 = ['N° Contrato','Vigencia','Estado','Contratista','Objeto',
                     'Fecha Inicio','Fecha Terminación','Valor ($)',
                     'Tipo Contrato','Clase','Dependencia'];

            foreach ($hd2 as $i => $h) {
                $ws2->setCellValueByColumnAndRow($i + 1, 1, $h);
            }

            $lastLetter2 = Coordinate::stringFromColumnIndex(count($hd2));
            $ws2->getStyle("A1:{$lastLetter2}1")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['argb' => self::COLOR_WHITE]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_HEADER_BG]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            ]);
            $ws2->getRowDimension(1)->setRowHeight(22);

            $r = 2;
            foreach ($contratos as $c) {
                $contratista = $c->nombre_persona_natural
                    ?? $c->nombre_representante_legal
                    ?? $c->razon_social
                    ?? '';

                $ws2->setCellValueByColumnAndRow(1,  $r, $c->numero_contrato);
                $ws2->setCellValueByColumnAndRow(2,  $r, $c->vigencia);
                $ws2->setCellValueByColumnAndRow(3,  $r, $c->estado);
                $ws2->setCellValueByColumnAndRow(4,  $r, $contratista);
                $ws2->setCellValueByColumnAndRow(5,  $r, $c->objeto);
                $ws2->setCellValueByColumnAndRow(6,  $r, $c->fecha_inicio?->format('d/m/Y'));
                $ws2->setCellValueByColumnAndRow(7,  $r, $c->fecha_terminacion?->format('d/m/Y'));
                $ws2->setCellValueByColumnAndRow(8,  $r, (float) ($c->valor_contrato ?? 0));
                $ws2->setCellValueByColumnAndRow(9,  $r, $c->tipo_contrato);
                $ws2->setCellValueByColumnAndRow(10, $r, $c->clase);
                $ws2->setCellValueByColumnAndRow(11, $r, $c->dependencia_contrato);

                if ($r % 2 === 0) {
                    $ws2->getStyle("A{$r}:{$lastLetter2}{$r}")->applyFromArray([
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::COLOR_ALT_ROW]],
                    ]);
                }
                $r++;
            }

            // Formato moneda columna H
            $ws2->getStyle("H2:H{$r}")
                ->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY);

            foreach (range(1, count($hd2)) as $ci) {
                $ws2->getColumnDimensionByColumn($ci)->setAutoSize(true);
            }

            // Freezar fila 1
            $ws2->freezePane('A2');
        }

        // Activar hoja Resumen
        $spreadsheet->setActiveSheetIndex(0);

        // ── Guardar en temp ──
        $tmpFile = tempnam(sys_get_temp_dir(), 'rpt_contratos_') . '.xlsx';
        (new Xlsx($spreadsheet))->save($tmpFile);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $tmpFile;
    }

    // ── Aplica formatos numéricos a columnas de la hoja Resumen ──
    private static function aplicarFormatosResumen($ws, array $encabezados, int $ultimaFila): void
    {
        $firstDataRow = 3;
        foreach ($encabezados as $i => $titulo) {
            $colLetter = Coordinate::stringFromColumnIndex($i + 1);
            $range     = "{$colLetter}{$firstDataRow}:{$colLetter}{$ultimaFila}";

            if (str_contains($titulo, 'Valor')) {
                $ws->getStyle($range)->getNumberFormat()->setFormatCode(self::FORMAT_CURRENCY);
            } elseif (str_contains($titulo, 'N°')) {
                $ws->getStyle($range)->getNumberFormat()->setFormatCode(self::FORMAT_INTEGER);
                $ws->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }
    }
}
