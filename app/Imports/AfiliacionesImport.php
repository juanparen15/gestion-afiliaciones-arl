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
        // Buscar o crear dependencia (actualizado para coincidir con plantilla)
        $dependencia = Dependencia::where('nombre', 'like', '%' . trim($row['secretaria_dependencia'] ?? '') . '%')
            ->orWhere('codigo', trim($row['secretaria_dependencia'] ?? ''))
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

        // Procesar tipo de documento
        $tipo_documento = strtoupper(trim($row['tipo_de_documento'] ?? 'CC'));
        if (!in_array($tipo_documento, ['CC', 'CE', 'PP', 'TI'])) {
            $tipo_documento = 'CC'; // Por defecto CC si es inválido
        }

        // Procesar nivel de riesgo (convertir texto a número romano)
        $nivel_riesgo = $this->procesarNivelRiesgo($row['nivel_de_riesgo'] ?? 'I');

        // Limpiar y procesar valores monetarios
        $valor_contrato = $this->limpiarValor($row['valor_del_contrato'] ?? 0);
        $honorarios = $this->limpiarValor($row['honorarios_mensual'] ?? 0);

        // Procesar IBC - puede venir del Excel o calcularse automáticamente
        $ibc_excel = $this->limpiarValor($row['ibc'] ?? 0);
        $ibc = $ibc_excel > 0 ? $ibc_excel : ($honorarios * 0.40);

        // Procesar meses y días (si están vacíos, poner 0 por defecto)
        $meses = !empty($row['meses']) && is_numeric($row['meses']) ? intval($row['meses']) : 0;
        $dias = !empty($row['dias']) && is_numeric($row['dias']) ? intval($row['dias']) : 0;

        // Procesar nombre ARL
        $nombre_arl = trim($row['nombre_arl'] ?? 'ARL SURA');
        if (empty($nombre_arl)) {
            $nombre_arl = 'ARL SURA';
        }

        // Preparar datos para insertar/actualizar
        $datos = [
            'numero_contrato' => trim($row['no_contrato'] ?? ''),
            'objeto_contractual' => trim($row['objeto_contrato'] ?? ''),
            'tipo_documento' => $tipo_documento,
            'numero_documento' => trim($row['cc_contratista'] ?? ''),
            'nombre_contratista' => trim($row['contratista'] ?? ''),
            'fecha_nacimiento' => $this->transformDate($row['fecha_de_nacimiento'] ?? null),
            'telefono_contratista' => trim($row['no_celular'] ?? ''),
            'valor_contrato' => $valor_contrato,
            'honorarios_mensual' => $honorarios,
            'ibc' => $ibc,
            'meses_contrato' => $meses,
            'dias_contrato' => $dias,
            'fecha_inicio' => $this->transformDate($row['fecha_ingreso_acta_inicio'] ?? null),
            'fecha_fin' => $this->transformDate($row['fecha_retiro'] ?? null),
            'tipo_riesgo' => $nivel_riesgo,
            'nombre_arl' => $nombre_arl,
            'barrio' => trim($row['barrio'] ?? ''),
            'direccion_residencia' => trim($row['direccion_residencia'] ?? ''),
            'eps' => trim($row['eps'] ?? ''),
            'afp' => trim($row['afp'] ?? ''),
            'email_contratista' => trim($row['correo_electronico'] ?? ''),
            'fecha_afiliacion_arl' => $this->transformDate($row['fecha_de_afiliacion_arl'] ?? null),
            'fecha_terminacion_afiliacion' => $this->transformDate($row['fecha_terminacion_afiliacion_arl'] ?? null),
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
            'secretaria_dependencia' => 'required',
            'valor_del_contrato' => 'required|numeric|min:0',

            // Campos obligatorios del contratista
            'tipo_de_documento' => 'required|in:CC,CE,PP,TI',
            'cc_contratista' => 'required',
            'contratista' => 'required',
            'fecha_de_nacimiento' => 'required',
            'no_celular' => 'required',

            // Campos obligatorios de fechas
            'fecha_ingreso_acta_inicio' => 'required',
            'fecha_retiro' => 'required',

            // Campos obligatorios financieros
            'honorarios_mensual' => 'required|numeric|min:0',
            'ibc' => 'required|numeric|min:0',

            // Campos obligatorios de ARL
            'nivel_de_riesgo' => 'required|in:1,2,3,4,5,I,II,III,IV,V',
            'nombre_arl' => 'required',

            // Campos opcionales pero con validación si existen
            'correo_electronico' => 'nullable|email',
            'meses' => 'nullable|integer|min:0',
            'dias' => 'nullable|integer|min:0',
            'area' => 'nullable',
            'barrio' => 'nullable',
            'direccion_residencia' => 'nullable',
            'eps' => 'nullable',
            'afp' => 'nullable',
            'fecha_de_afiliacion_arl' => 'nullable',
            'fecha_terminacion_afiliacion_arl' => 'nullable',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            // Mensajes para campos del contrato
            'no_contrato.required' => 'El número de contrato es obligatorio',
            'objeto_contrato.required' => 'El objeto del contrato es obligatorio',
            'secretaria_dependencia.required' => 'La secretaría/dependencia es obligatoria',
            'valor_del_contrato.required' => 'El valor del contrato es obligatorio',
            'valor_del_contrato.numeric' => 'El valor del contrato debe ser un número',
            'valor_del_contrato.min' => 'El valor del contrato debe ser mayor a 0',

            // Mensajes para campos del contratista
            'tipo_de_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_de_documento.in' => 'El tipo de documento debe ser CC, CE, PP o TI',
            'cc_contratista.required' => 'El número de documento del contratista es obligatorio',
            'contratista.required' => 'El nombre completo del contratista es obligatorio',
            'fecha_de_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
            'no_celular.required' => 'El número de celular es obligatorio',

            // Mensajes para campos de fechas
            'fecha_ingreso_acta_inicio.required' => 'La fecha de inicio (acta inicio) es obligatoria',
            'fecha_retiro.required' => 'La fecha de retiro/finalización es obligatoria',

            // Mensajes para campos financieros
            'honorarios_mensual.required' => 'Los honorarios mensuales son obligatorios',
            'honorarios_mensual.numeric' => 'Los honorarios deben ser un número válido',
            'honorarios_mensual.min' => 'Los honorarios deben ser mayores a 0',
            'ibc.required' => 'El IBC (Ingreso Base de Cotización) es obligatorio',
            'ibc.numeric' => 'El IBC debe ser un número válido',
            'ibc.min' => 'El IBC debe ser mayor a 0',

            // Mensajes para campos de ARL
            'nivel_de_riesgo.required' => 'El nivel de riesgo es obligatorio',
            'nivel_de_riesgo.in' => 'El nivel de riesgo debe ser 1, 2, 3, 4, 5 o I, II, III, IV, V',
            'nombre_arl.required' => 'El nombre de la ARL es obligatorio',

            // Mensajes para campos opcionales
            'correo_electronico.email' => 'El correo electrónico no tiene un formato válido',
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
