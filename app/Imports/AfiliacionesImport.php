<?php

namespace App\Imports;

use App\Models\Afiliacion;
use App\Models\Dependencia;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Illuminate\Support\Facades\Auth;

class AfiliacionesImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnFailure
{
    use SkipsFailures;

    public $registrosCreados = 0;
    public $registrosActualizados = 0;

    public function model(array $row)
    {
        // Buscar o crear dependencia
        $dependencia = Dependencia::where('nombre', 'like', '%' . trim($row['secretaria'] ?? '') . '%')
            ->orWhere('codigo', trim($row['secretaria'] ?? ''))
            ->first();

        if (!$dependencia) {
            $dependencia = Dependencia::first(); // Usar primera dependencia por defecto
        }

        // Buscar o asignar área
        $area = null;
        if ($dependencia && isset($row['area'])) {
            $area = \App\Models\Area::where('dependencia_id', $dependencia->id)
                ->where(function($q) use ($row) {
                    $q->where('nombre', 'like', '%' . trim($row['area']) . '%')
                      ->orWhere('codigo', trim($row['area']));
                })
                ->first();
        }

        // Procesar nivel de riesgo (convertir texto a número romano)
        $nivel_riesgo = $this->procesarNivelRiesgo($row['nivel_de_riesgo'] ?? '1');

        // Limpiar y procesar valores monetarios
        $valor_contrato = $this->limpiarValor($row['valor_del_contrato'] ?? 0);
        $honorarios = $this->limpiarValor($row['honorarios_mensual'] ?? 0);

        // IMPORTANTE: El IBC SIEMPRE se calcula automáticamente como 40% de los honorarios
        // No se debe tomar del Excel para evitar errores
        $ibc = $honorarios * 0.40;

        // Procesar meses y días (si están vacíos, poner 0 por defecto)
        $meses = !empty($row['meses']) && is_numeric($row['meses']) ? intval($row['meses']) : 0;
        $dias = !empty($row['dias']) && is_numeric($row['dias']) ? intval($row['dias']) : 0;

        // Preparar datos para insertar/actualizar
        $datos = [
            'numero_contrato' => trim($row['no_contrato'] ?? ''),
            'objeto_contractual' => trim($row['objeto_contrato'] ?? ''),
            'numero_documento' => trim($row['cc_contratista'] ?? ''),
            'nombre_contratista' => trim($row['contratista'] ?? ''),
            'tipo_documento' => 'CC',
            'valor_contrato' => $valor_contrato,
            'meses_contrato' => $meses,
            'dias_contrato' => $dias,
            'honorarios_mensual' => $honorarios,
            'ibc' => $ibc,
            'fecha_inicio' => $this->transformDate($row['fecha_ingreso_a_partir_de_acta_inicio'] ?? null),
            'fecha_fin' => $this->transformDate($row['fecha_retiro'] ?? null),
            'fecha_nacimiento' => $this->transformDate($row['fecha_de_nacimiento'] ?? null),
            'tipo_riesgo' => $nivel_riesgo,
            'telefono_contratista' => trim($row['no_celular'] ?? ''),
            'barrio' => trim($row['barrio'] ?? ''),
            'direccion_residencia' => trim($row['direccion_residencia'] ?? ''),
            'eps' => trim($row['eps'] ?? ''),
            'afp' => trim($row['afp'] ?? ''),
            'email_contratista' => trim($row['direccion_de_correo_electronica'] ?? ''),
            'fecha_afiliacion_arl' => $this->transformDate($row['fecha_de_afiliacion'] ?? null),
            'fecha_terminacion_afiliacion' => $this->transformDate($row['fecha_termiancion_afiliacion'] ?? null),
            'nombre_arl' => 'ARL SURA', // Valor por defecto
            'dependencia_id' => $dependencia->id,
            'area_id' => $area?->id,
            'estado' => 'pendiente',
        ];

        // Buscar si existe un registro con este número de documento (incluyendo eliminados)
        $numeroDocumento = trim($row['cc_contratista'] ?? '');
        $afiliacionExistente = Afiliacion::withTrashed()
            ->where('numero_documento', $numeroDocumento)
            ->first();

        if ($afiliacionExistente) {
            // Si el registro fue eliminado (soft deleted), restaurarlo
            if ($afiliacionExistente->trashed()) {
                $afiliacionExistente->restore();
            }

            // Actualizar el registro existente con los nuevos datos
            $afiliacionExistente->update($datos);

            $this->registrosActualizados++;

            return null; // No crear nuevo registro
        }

        // Si no existe, crear uno nuevo con created_by
        $datos['created_by'] = Auth::id();
        $this->registrosCreados++;

        return new Afiliacion($datos);
    }

    public function rules(): array
    {
        return [
            // Campos obligatorios del contrato
            'no_contrato' => 'required',
            'objeto_contrato' => 'required',
            'secretaria' => 'required',
            'valor_del_contrato' => 'required|numeric|min:0',

            // Campos obligatorios del contratista
            'cc_contratista' => 'required',
            'contratista' => 'required',

            // Campos obligatorios de fechas
            'fecha_ingreso_a_partir_de_acta_inicio' => 'required',
            'fecha_retiro' => 'required',

            // Campos obligatorios financieros
            'honorarios_mensual' => 'required|numeric|min:0',

            // Campos opcionales pero con validación si existen
            'no_celular' => 'nullable',
            'direccion_de_correo_electronica' => 'nullable|email',
            'nivel_de_riesgo' => 'nullable|in:1,2,3,4,5,I,II,III,IV,V',
            'meses' => 'nullable|integer|min:0',
            'dias' => 'nullable|integer|min:0',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'no_contrato.required' => 'El número de contrato es obligatorio',
            'objeto_contrato.required' => 'El objeto del contrato es obligatorio',
            'secretaria.required' => 'La secretaría/dependencia es obligatoria',
            'valor_del_contrato.required' => 'El valor del contrato es obligatorio',
            'valor_del_contrato.numeric' => 'El valor del contrato debe ser un número',
            'valor_del_contrato.min' => 'El valor del contrato debe ser mayor a 0',
            'cc_contratista.required' => 'La cédula del contratista es obligatoria',
            'contratista.required' => 'El nombre del contratista es obligatorio',
            'fecha_ingreso_a_partir_de_acta_inicio.required' => 'La fecha de inicio es obligatoria',
            'fecha_retiro.required' => 'La fecha de retiro/fin es obligatoria',
            'honorarios_mensual.required' => 'Los honorarios mensuales son obligatorios',
            'honorarios_mensual.numeric' => 'Los honorarios deben ser un número',
            'honorarios_mensual.min' => 'Los honorarios deben ser mayor a 0',
            'direccion_de_correo_electronica.email' => 'El correo electrónico no tiene un formato válido',
            'nivel_de_riesgo.in' => 'El nivel de riesgo debe estar entre 1-5 o I-V',
            'meses.integer' => 'Los meses deben ser un número entero',
            'meses.min' => 'Los meses deben ser 0 o mayor',
            'dias.integer' => 'Los días deben ser un número entero',
            'dias.min' => 'Los días deben ser 0 o mayor',
        ];
    }

    private function transformDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Si es un número de serie de Excel
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }

            // Si es una fecha en formato dd/mm/yyyy o similar
            if (is_string($value)) {
                // Intentar varios formatos
                $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];
                foreach ($formats as $format) {
                    try {
                        return Carbon::createFromFormat($format, $value);
                    } catch (\Exception $e) {
                        continue;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function limpiarValor($valor)
    {
        if (is_numeric($valor)) {
            return floatval($valor);
        }

        // Eliminar símbolos de moneda, espacios, puntos de miles
        $valor = preg_replace('/[^\d,.-]/', '', $valor);
        $valor = str_replace(',', '.', $valor);

        return floatval($valor);
    }

    private function procesarNivelRiesgo($nivel)
    {
        $nivel = strtoupper(trim($nivel));

        // Si ya es romano, devolverlo
        if (in_array($nivel, ['I', 'II', 'III', 'IV', 'V'])) {
            return $nivel;
        }

        // Mapear números a romanos
        $mapa = [
            '1' => 'I',
            '2' => 'II',
            '3' => 'III',
            '4' => 'IV',
            '5' => 'V',
        ];

        return $mapa[$nivel] ?? 'I';
    }

    public function headingRow(): int
    {
        return 2; // Los encabezados están en la fila 2 del Excel
    }
}
