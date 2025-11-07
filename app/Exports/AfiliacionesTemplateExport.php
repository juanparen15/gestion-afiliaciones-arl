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
                'CC CONTRATISTA *',
                'CONTRATISTA *',
                'VALOR DEL CONTRATO *',
                'MESES',
                'DIAS',
                'Honorarios mensual *',
                'Fecha ingreso A partir de Acta inicio *',
                'Fecha retiro *',
                'Secretaría *',
                'Área',
                'Fecha de Nacimiento',
                'Nivel de riesgo',
                'No. Celular',
                'Barrio',
                'Dirección Residencia',
                'EPS',
                'AFP',
                'Dirección de correo Electronica',
                'FECHA DE AFILIACION',
                'FECHA TERMIANCION AFILIACION',
            ],
            [
                'Ej: 001-2025',
                'Descripción del contrato',
                'Solo números sin puntos',
                'Nombre completo',
                'Solo números, sin $ ni puntos',
                'Ej: 6 (dejar vacío = 0 automático)',
                'Ej: 15 (dejar vacío = 0 automático)',
                'Solo números, sin $ ni puntos',
                'dd/mm/aaaa',
                'dd/mm/aaaa',
                'Nombre de la secretaría',
                'Nombre del área (opcional)',
                'dd/mm/aaaa (opcional)',
                '1, 2, 3, 4 o 5 (opcional, por defecto I)',
                'Ej: 3001234567 (opcional)',
                'Barrio de residencia (opcional)',
                'Dirección completa (opcional)',
                'Nombre de la EPS (opcional)',
                'Nombre del fondo de pensiones (opcional)',
                'correo@ejemplo.com (opcional)',
                'dd/mm/aaaa (opcional)',
                'dd/mm/aaaa (opcional)',
            ],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar celdas de la primera fila (título)
        $sheet->mergeCells('A1:V1');

        // Ajustar altura de las filas
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(30);
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(18);  // No. CONTRATO
        $sheet->getColumnDimension('B')->setWidth(50);  // OBJETO CONTRATO
        $sheet->getColumnDimension('C')->setWidth(18);  // CC CONTRATISTA
        $sheet->getColumnDimension('D')->setWidth(40);  // CONTRATISTA
        $sheet->getColumnDimension('E')->setWidth(18);  // VALOR DEL CONTRATO
        $sheet->getColumnDimension('F')->setWidth(10);  // MESES
        $sheet->getColumnDimension('G')->setWidth(10);  // DIAS
        $sheet->getColumnDimension('H')->setWidth(18);  // Honorarios mensual
        $sheet->getColumnDimension('I')->setWidth(25);  // Fecha ingreso
        $sheet->getColumnDimension('J')->setWidth(25);  // Fecha retiro
        $sheet->getColumnDimension('K')->setWidth(30);  // Secretaría
        $sheet->getColumnDimension('L')->setWidth(30);  // Área
        $sheet->getColumnDimension('M')->setWidth(20);  // Fecha de Nacimiento
        $sheet->getColumnDimension('N')->setWidth(35);  // Nivel de riesgo
        $sheet->getColumnDimension('O')->setWidth(18);  // No. Celular
        $sheet->getColumnDimension('P')->setWidth(20);  // Barrio
        $sheet->getColumnDimension('Q')->setWidth(35);  // Dirección Residencia
        $sheet->getColumnDimension('R')->setWidth(25);  // EPS
        $sheet->getColumnDimension('S')->setWidth(25);  // AFP
        $sheet->getColumnDimension('T')->setWidth(30);  // Correo Electronico
        $sheet->getColumnDimension('U')->setWidth(20);  // FECHA DE AFILIACION
        $sheet->getColumnDimension('V')->setWidth(20);  // FECHA TERMINACION

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
