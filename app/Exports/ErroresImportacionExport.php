<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ErroresImportacionExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $errores;

    public function __construct($errores)
    {
        $this->errores = $errores;
    }

    public function collection()
    {
        return collect($this->errores);
    }

    public function headings(): array
    {
        return [
            'Fila Excel',
            'Campo con Error',
            'Descripción del Error',
            'Valor Actual',
            'Acción Requerida',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3366CC'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Aplicar bordes y alineación a todas las celdas con datos
            'A2:E' . (count($this->errores) + 1) => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_TOP,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Fila Excel
            'B' => 25,  // Campo con Error
            'C' => 40,  // Descripción del Error
            'D' => 25,  // Valor Actual
            'E' => 40,  // Acción Requerida
        ];
    }
}
