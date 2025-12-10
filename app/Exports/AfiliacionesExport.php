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
                'SUPERVISOR DEL CONTRATO',
                'VALOR DEL CONTRATO',
                'MESES',
                'DIAS',
                'Honorarios mensual',
                'IBC',
                'Fecha ingreso A partir de Acta inicio',
                'Fecha retiro',
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
            $afiliacion->supervisor_contrato,
            $afiliacion->valor_contrato,
            $afiliacion->meses_contrato,
            $afiliacion->dias_contrato,
            $afiliacion->honorarios_mensual,
            $afiliacion->ibc,
            $afiliacion->fecha_inicio?->format('d/m/Y'),
            $afiliacion->fecha_fin?->format('d/m/Y'),
            $afiliacion->tiene_adicion ? 'SÍ' : 'NO',
            $afiliacion->descripcion_adicion,
            $afiliacion->valor_adicion,
            $afiliacion->fecha_adicion?->format('d/m/Y'),
            $afiliacion->tiene_prorroga ? 'SÍ' : 'NO',
            $afiliacion->descripcion_prorroga,
            $afiliacion->meses_prorroga,
            $afiliacion->dias_prorroga,
            $afiliacion->nueva_fecha_fin_prorroga?->format('d/m/Y'),
            $afiliacion->tiene_terminacion_anticipada ? 'SÍ' : 'NO',
            $afiliacion->fecha_terminacion_anticipada?->format('d/m/Y'),
            $afiliacion->motivo_terminacion_anticipada,
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
        $sheet->mergeCells('A1:AL1');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(15);  // No. CONTRATO
        $sheet->getColumnDimension('B')->setWidth(50);  // OBJETO CONTRATO
        $sheet->getColumnDimension('C')->setWidth(15);  // CC CONTRATISTA
        $sheet->getColumnDimension('D')->setWidth(40);  // CONTRATISTA
        $sheet->getColumnDimension('E')->setWidth(30);  // SUPERVISOR DEL CONTRATO
        $sheet->getColumnDimension('F')->setWidth(15);  // VALOR DEL CONTRATO
        $sheet->getColumnDimension('G')->setWidth(8);   // MESES
        $sheet->getColumnDimension('H')->setWidth(8);   // DIAS
        $sheet->getColumnDimension('I')->setWidth(15);  // Honorarios mensual
        $sheet->getColumnDimension('J')->setWidth(15);  // IBC
        $sheet->getColumnDimension('K')->setWidth(20);  // Fecha ingreso
        $sheet->getColumnDimension('L')->setWidth(20);  // Fecha retiro
        $sheet->getColumnDimension('M')->setWidth(15);  // TIENE ADICIÓN
        $sheet->getColumnDimension('N')->setWidth(40);  // DESCRIPCIÓN ADICIÓN
        $sheet->getColumnDimension('O')->setWidth(15);  // VALOR ADICIÓN
        $sheet->getColumnDimension('P')->setWidth(20);  // FECHA ADICIÓN
        $sheet->getColumnDimension('Q')->setWidth(15);  // TIENE PRÓRROGA
        $sheet->getColumnDimension('R')->setWidth(40);  // DESCRIPCIÓN PRÓRROGA
        $sheet->getColumnDimension('S')->setWidth(12);  // MESES PRÓRROGA
        $sheet->getColumnDimension('T')->setWidth(12);  // DÍAS PRÓRROGA
        $sheet->getColumnDimension('U')->setWidth(20);  // NUEVA FECHA FIN PRÓRROGA
        $sheet->getColumnDimension('V')->setWidth(25);  // TIENE TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('W')->setWidth(25);  // FECHA TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('X')->setWidth(40);  // MOTIVO TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('Y')->setWidth(30);  // Secretaría
        $sheet->getColumnDimension('Z')->setWidth(30);  // Área
        $sheet->getColumnDimension('AA')->setWidth(20); // Fecha de Nacimiento
        $sheet->getColumnDimension('AB')->setWidth(15); // Nivel de riesgo
        $sheet->getColumnDimension('AC')->setWidth(15); // No. Celular
        $sheet->getColumnDimension('AD')->setWidth(20); // Barrio
        $sheet->getColumnDimension('AE')->setWidth(30); // Dirección Residencia
        $sheet->getColumnDimension('AF')->setWidth(20); // EPS
        $sheet->getColumnDimension('AG')->setWidth(20); // AFP
        $sheet->getColumnDimension('AH')->setWidth(30); // Dirección de correo Electronica
        $sheet->getColumnDimension('AI')->setWidth(20); // FECHA DE AFILIACION
        $sheet->getColumnDimension('AJ')->setWidth(25); // FECHA TERMINACION AFILIACION
        $sheet->getColumnDimension('AK')->setWidth(20); // ARL
        $sheet->getColumnDimension('AL')->setWidth(15); // Estado

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
