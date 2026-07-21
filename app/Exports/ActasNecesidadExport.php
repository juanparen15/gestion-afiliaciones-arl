<?php

namespace App\Exports;

use App\Models\ActaNecesidad;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActasNecesidadExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(protected $query = null) {}

    public function query()
    {
        return $this->query ?? ActaNecesidad::query()->with(['dependencia', 'area', 'aprobador'])->orderBy('consecutivo');
    }

    public function headings(): array
    {
        return [
            'No. ACTA', 'ESTADO', 'CORREO SOLICITANTE', 'DEPENDENCIA', 'ÁREA', 'NOMBRE SOLICITANTE',
            'OBJETO DEL CONTRATO', 'TIPO DE CONTRATO', 'DURACIÓN', 'MODALIDAD DE SELECCIÓN',
            'TIPO DE SOLICITUD', 'No. CONTRATO O CONVENIO', 'PRESUPUESTO OFICIAL', 'BPIM - BPIN',
            'CÓDIGO PAA', 'OBSERVACIONES', 'FECHA SOLICITUD', 'FECHA APROBADO', 'GESTIONADO POR',
            'MOTIVO RECHAZO/ANULACIÓN', 'CÓDIGO VERIFICACIÓN',
        ];
    }

    public function map($a): array
    {
        return [
            $a->consecutivo ? '0' . $a->consecutivo : '',
            ucfirst($a->estado),
            $a->email_solicitante,
            $a->dependencia_texto,
            $a->area_texto,
            $a->nombre_solicitante,
            $a->objeto_contrato,
            $a->tipo_contrato,
            $a->duracion,
            $a->modalidad_seleccion,
            $a->tipo_solicitud,
            $a->numero_contrato_convenio,
            $a->presupuesto_oficial ? number_format((float) $a->presupuesto_oficial, 0, ',', '.') : '',
            $a->codigo_bpim_bpin,
            $a->codigo_paa,
            $a->observaciones,
            optional($a->fecha_solicitud)->format('d/m/Y H:i'),
            optional($a->fecha_aprobado)->format('d/m/Y H:i'),
            $a->aprobador?->name,
            $a->motivo_rechazo ?: $a->motivo_anulacion,
            $a->codigo_verificacion,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '16A34A']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
            ],
        ];
    }

    public function title(): string
    {
        return 'Actas de Necesidad';
    }
}
