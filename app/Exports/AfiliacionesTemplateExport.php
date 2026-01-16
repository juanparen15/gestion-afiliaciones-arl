<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AfiliacionesTemplateExport implements WithHeadings, WithStyles, WithTitle
{
    public function headings(): array
    {
        return [
            [
                'SISTEMA DE GESTIÓN DE AFILIACIONES ARL - PLANTILLA DE IMPORTACIÓN',
            ],
            [
                'No. CONTRATO *',
                'OBJETO CONTRATO *',
                'TIPO DE DOCUMENTO *',
                'CC CONTRATISTA *',
                'CONTRATISTA *',
                'SUPERVISOR DEL CONTRATO *',
                'FECHA DE NACIMIENTO *',
                'No. CELULAR *',
                'VALOR DEL CONTRATO *',
                'HONORARIOS MENSUAL *',
                'IBC *',
                'MESES *',
                'DIAS *',
                'FECHA INGRESO (ACTA INICIO) *',
                'FECHA RETIRO *',
                'TIENE ADICIÓN',
                'DESCRIPCIÓN ADICIÓN',
                'VALOR ADICIÓN',
                'FECHA ADICIÓN',
                'TIENE PRÓRROGA',
                'DESCRIPCIÓN PRÓRROGA',
                'MESES PRÓRROGA',
                'DÍAS PRÓRROGA',
                'NUEVA FECHA FIN PRÓRROGA',
                'TIENE TERMINACIÓN ANTICIPADA',
                'FECHA TERMINACIÓN ANTICIPADA',
                'MOTIVO TERMINACIÓN ANTICIPADA',
                'SECRETARÍA / DEPENDENCIA *',
                'ÁREA',
                'NIVEL DE RIESGO *',
                'NOMBRE ARL *',
                'BARRIO *',
                'DIRECCIÓN RESIDENCIA *',
                'EPS *',
                'AFP *',
                'CORREO ELECTRÓNICO *',
                'FECHA DE AFILIACIÓN ARL *',
                'FECHA TERMINACIÓN AFILIACIÓN ARL *',
            ],
            [
                'Número del contrato. Ej: 001-2025',
                'Descripción detallada del objeto contractual',
                'CC, CE, PP o TI',
                'Solo números, sin puntos ni comas. Ej: 1234567890',
                'Nombre completo del contratista',
                'Nombre del supervisor del contrato',
                'Formato: dd/mm/aaaa. Ej: 15/03/1985',
                'Número celular. Ej: 3001234567',
                'Solo números, sin símbolos $ ni puntos. Ej: 5000000',
                'Solo números, sin símbolos $ ni puntos. Ej: 1500000',
                'Ingreso Base de Cotización (40% de honorarios). Ej: 600000',
                'Número de meses del contrato. Ej: 6',
                'Número de días del contrato. Ej: 15',
                'Fecha inicio del contrato. Formato: dd/mm/aaaa',
                'Fecha finalización del contrato. Formato: dd/mm/aaaa',
                'Sí o No (vacío = No)',
                'Descripción de la adición (opcional)',
                'Valor de la adición sin símbolos (opcional)',
                'Fecha de la adición. Formato: dd/mm/aaaa (opcional)',
                'Sí o No (vacío = No)',
                'Descripción de la prórroga (opcional)',
                'Meses de prórroga (opcional)',
                'Días de prórroga (opcional)',
                'Nueva fecha fin con prórroga. Formato: dd/mm/aaaa (opcional)',
                'Sí o No (vacío = No)',
                'Fecha terminación anticipada. Formato: dd/mm/aaaa (opcional)',
                'Motivo de terminación anticipada (opcional)',
                'Nombre exacto de la secretaría o dependencia',
                'Nombre del área (opcional si no tiene área asignada)',
                'I, II, III, IV o V',
                'Nombre de la ARL. Por defecto: ARL SURA',
                'Barrio de residencia',
                'Dirección completa de residencia',
                'Nombre de la EPS',
                'Nombre del fondo de pensiones AFP',
                'Correo electrónico. Ej: correo@ejemplo.com',
                'Fecha de afiliación en ARL. Formato: dd/mm/aaaa',
                'Fecha de terminación de afiliación ARL. Formato: dd/mm/aaaa',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar celdas de la primera fila (título)
        $sheet->mergeCells('A1:AL1');

        // Ajustar altura de las filas
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(35);
        $sheet->getRowDimension(3)->setRowHeight(50);

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(20);  // No. CONTRATO *
        $sheet->getColumnDimension('B')->setWidth(50);  // OBJETO CONTRATO *
        $sheet->getColumnDimension('C')->setWidth(20);  // TIPO DE DOCUMENTO *
        $sheet->getColumnDimension('D')->setWidth(20);  // CC CONTRATISTA *
        $sheet->getColumnDimension('E')->setWidth(40);  // CONTRATISTA *
        $sheet->getColumnDimension('F')->setWidth(30);  // SUPERVISOR DEL CONTRATO *
        $sheet->getColumnDimension('G')->setWidth(22);  // FECHA DE NACIMIENTO *
        $sheet->getColumnDimension('H')->setWidth(18);  // No. CELULAR *
        $sheet->getColumnDimension('I')->setWidth(22);  // VALOR DEL CONTRATO *
        $sheet->getColumnDimension('J')->setWidth(22);  // HONORARIOS MENSUAL *
        $sheet->getColumnDimension('K')->setWidth(22);  // IBC *
        $sheet->getColumnDimension('L')->setWidth(12);  // MESES *
        $sheet->getColumnDimension('M')->setWidth(12);  // DIAS *
        $sheet->getColumnDimension('N')->setWidth(28);  // FECHA INGRESO *
        $sheet->getColumnDimension('O')->setWidth(22);  // FECHA RETIRO *
        $sheet->getColumnDimension('P')->setWidth(18);  // TIENE ADICIÓN
        $sheet->getColumnDimension('Q')->setWidth(40);  // DESCRIPCIÓN ADICIÓN
        $sheet->getColumnDimension('R')->setWidth(18);  // VALOR ADICIÓN
        $sheet->getColumnDimension('S')->setWidth(22);  // FECHA ADICIÓN
        $sheet->getColumnDimension('T')->setWidth(18);  // TIENE PRÓRROGA
        $sheet->getColumnDimension('U')->setWidth(40);  // DESCRIPCIÓN PRÓRROGA
        $sheet->getColumnDimension('V')->setWidth(15);  // MESES PRÓRROGA
        $sheet->getColumnDimension('W')->setWidth(15);  // DÍAS PRÓRROGA
        $sheet->getColumnDimension('X')->setWidth(28);  // NUEVA FECHA FIN PRÓRROGA
        $sheet->getColumnDimension('Y')->setWidth(28);  // TIENE TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('Z')->setWidth(28);  // FECHA TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('AA')->setWidth(40); // MOTIVO TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('AB')->setWidth(35); // SECRETARÍA *
        $sheet->getColumnDimension('AC')->setWidth(30); // ÁREA
        $sheet->getColumnDimension('AD')->setWidth(20); // NIVEL DE RIESGO *
        $sheet->getColumnDimension('AE')->setWidth(22); // NOMBRE ARL *
        $sheet->getColumnDimension('AF')->setWidth(25); // BARRIO *
        $sheet->getColumnDimension('AG')->setWidth(35); // DIRECCIÓN RESIDENCIA *
        $sheet->getColumnDimension('AH')->setWidth(25); // EPS *
        $sheet->getColumnDimension('AI')->setWidth(25); // AFP *
        $sheet->getColumnDimension('AJ')->setWidth(30); // CORREO ELECTRÓNICO *
        $sheet->getColumnDimension('AK')->setWidth(28); // FECHA DE AFILIACIÓN ARL *
        $sheet->getColumnDimension('AL')->setWidth(32); // FECHA TERMINACIÓN AFILIACIÓN ARL *

        return [
            // Estilo para el título (Fila 1)
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3366CC'],
                ],
            ],
            // Estilo para los encabezados de columnas (Fila 2)
            2 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FF6600'],
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
            // Estilo para los ejemplos (Fila 3)
            3 => [
                'font' => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F0F0F0'],
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Plantilla Afiliaciones';
    }
}
