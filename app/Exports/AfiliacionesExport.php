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
        return $this->query ?? Afiliacion::query()->with(['dependencia', 'area', 'novedadRegistradaPor']);
    }

    public function headings(): array
    {
        return [
            [
                'SISTEMA DE GESTIÓN DE AFILIACIONES ARL',
            ],
            [
                'No. CONTRATO',
                'No. REGISTRO PRESUPUESTAL',
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
                'FECHA REGISTRO NOVEDAD',
                'NOVEDAD REGISTRADA POR',
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
            $afiliacion->numero_registro_presupuestal,
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
            $afiliacion->novedad_registrada_at?->format('d/m/Y H:i'),
            $afiliacion->novedadRegistradaPor?->name,
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
        $sheet->mergeCells('A1:AO1');

        // Ajustar ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(15);  // No. CONTRATO
        $sheet->getColumnDimension('B')->setWidth(25);  // No. REGISTRO PRESUPUESTAL
        $sheet->getColumnDimension('C')->setWidth(50);  // OBJETO CONTRATO
        $sheet->getColumnDimension('D')->setWidth(15);  // CC CONTRATISTA
        $sheet->getColumnDimension('E')->setWidth(40);  // CONTRATISTA
        $sheet->getColumnDimension('F')->setWidth(30);  // SUPERVISOR DEL CONTRATO
        $sheet->getColumnDimension('G')->setWidth(15);  // VALOR DEL CONTRATO
        $sheet->getColumnDimension('H')->setWidth(8);   // MESES
        $sheet->getColumnDimension('I')->setWidth(8);   // DIAS
        $sheet->getColumnDimension('J')->setWidth(15);  // Honorarios mensual
        $sheet->getColumnDimension('K')->setWidth(15);  // IBC
        $sheet->getColumnDimension('L')->setWidth(20);  // Fecha ingreso
        $sheet->getColumnDimension('M')->setWidth(20);  // Fecha retiro
        $sheet->getColumnDimension('N')->setWidth(15);  // TIENE ADICIÓN
        $sheet->getColumnDimension('O')->setWidth(40);  // DESCRIPCIÓN ADICIÓN
        $sheet->getColumnDimension('P')->setWidth(15);  // VALOR ADICIÓN
        $sheet->getColumnDimension('Q')->setWidth(20);  // FECHA ADICIÓN
        $sheet->getColumnDimension('R')->setWidth(15);  // TIENE PRÓRROGA
        $sheet->getColumnDimension('S')->setWidth(40);  // DESCRIPCIÓN PRÓRROGA
        $sheet->getColumnDimension('T')->setWidth(12);  // MESES PRÓRROGA
        $sheet->getColumnDimension('U')->setWidth(12);  // DÍAS PRÓRROGA
        $sheet->getColumnDimension('V')->setWidth(20);  // NUEVA FECHA FIN PRÓRROGA
        $sheet->getColumnDimension('W')->setWidth(22);  // FECHA REGISTRO NOVEDAD
        $sheet->getColumnDimension('X')->setWidth(30);  // NOVEDAD REGISTRADA POR
        $sheet->getColumnDimension('Y')->setWidth(25);  // TIENE TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('Z')->setWidth(25);  // FECHA TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('AA')->setWidth(40); // MOTIVO TERMINACIÓN ANTICIPADA
        $sheet->getColumnDimension('AB')->setWidth(30); // Secretaría
        $sheet->getColumnDimension('AC')->setWidth(30); // Área
        $sheet->getColumnDimension('AD')->setWidth(20); // Fecha de Nacimiento
        $sheet->getColumnDimension('AE')->setWidth(15); // Nivel de riesgo
        $sheet->getColumnDimension('AF')->setWidth(15); // No. Celular
        $sheet->getColumnDimension('AG')->setWidth(20); // Barrio
        $sheet->getColumnDimension('AH')->setWidth(30); // Dirección Residencia
        $sheet->getColumnDimension('AI')->setWidth(20); // EPS
        $sheet->getColumnDimension('AJ')->setWidth(20); // AFP
        $sheet->getColumnDimension('AK')->setWidth(30); // Dirección de correo Electronica
        $sheet->getColumnDimension('AL')->setWidth(20); // FECHA DE AFILIACION
        $sheet->getColumnDimension('AM')->setWidth(25); // FECHA TERMINACION AFILIACION
        $sheet->getColumnDimension('AN')->setWidth(20); // ARL
        $sheet->getColumnDimension('AO')->setWidth(15); // Estado
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
