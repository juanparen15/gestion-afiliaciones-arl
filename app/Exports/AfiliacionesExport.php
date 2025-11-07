<?php

namespace App\Exports;

use App\Models\Afiliacion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AfiliacionesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    protected $query;

    public function __construct($query = null)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query ?? Afiliacion::query()->with(['dependencia', 'area']);
    }

    public function headings(): array
    {
        return [
            [
                'SISTEMA DE GESTIÓN DE AFILIACIONES ARL',
            ],
            [
                'No. CONTRATO',
                'OBJETO CONTRATO',
                'CC CONTRATISTA',
                'CONTRATISTA',
                'VALOR DEL CONTRATO',
                'MESES',
                'DIAS',
                'Honorarios mensual',
                'IBC',
                'Fecha ingreso A partir de Acta inicio',
                'Fecha retiro',
                'Secretaría',
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
                'ARL',
                'Estado',
            ],
        ];
    }

    public function map($afiliacion): array
    {
        return [
            $afiliacion->numero_contrato,
            $afiliacion->objeto_contractual,
            $afiliacion->numero_documento,
            $afiliacion->nombre_contratista,
            $afiliacion->valor_contrato,
            $afiliacion->meses_contrato,
            $afiliacion->dias_contrato,
            $afiliacion->honorarios_mensual,
            $afiliacion->ibc,
            $afiliacion->fecha_inicio?->format('d/m/Y'),
            $afiliacion->fecha_fin?->format('d/m/Y'),
            $afiliacion->dependencia?->nombre,
            $afiliacion->area?->nombre,
            $afiliacion->fecha_nacimiento?->format('d/m/Y'),
            $afiliacion->tipo_riesgo,
            $afiliacion->telefono_contratista,
            $afiliacion->barrio,
            $afiliacion->direccion_residencia,
            $afiliacion->eps,
            $afiliacion->afp,
            $afiliacion->email_contratista,
            $afiliacion->fecha_afiliacion_arl?->format('d/m/Y'),
            $afiliacion->fecha_terminacion_afiliacion?->format('d/m/Y'),
            $afiliacion->nombre_arl,
            match($afiliacion->estado) {
                'pendiente' => 'Pendiente',
                'validado' => 'Validado',
                'rechazado' => 'Rechazado',
                default => $afiliacion->estado,
            },
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Combinar celdas de la primera fila (título)
        $sheet->mergeCells('A1:Y1');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(50);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(40);
        $sheet->getColumnDimension('E')->setWidth(15);
        $sheet->getColumnDimension('F')->setWidth(8);
        $sheet->getColumnDimension('G')->setWidth(8);
        $sheet->getColumnDimension('H')->setWidth(15);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(20);
        $sheet->getColumnDimension('K')->setWidth(20);
        $sheet->getColumnDimension('L')->setWidth(30);
        $sheet->getColumnDimension('M')->setWidth(30);
        $sheet->getColumnDimension('N')->setWidth(20);
        $sheet->getColumnDimension('O')->setWidth(15);
        $sheet->getColumnDimension('P')->setWidth(15);
        $sheet->getColumnDimension('Q')->setWidth(20);
        $sheet->getColumnDimension('R')->setWidth(30);
        $sheet->getColumnDimension('S')->setWidth(20);
        $sheet->getColumnDimension('T')->setWidth(20);
        $sheet->getColumnDimension('U')->setWidth(30);
        $sheet->getColumnDimension('V')->setWidth(20);
        $sheet->getColumnDimension('W')->setWidth(20);
        $sheet->getColumnDimension('X')->setWidth(20);
        $sheet->getColumnDimension('Y')->setWidth(15);

        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFA500'],
                ],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Afiliaciones ARL';
    }
}
