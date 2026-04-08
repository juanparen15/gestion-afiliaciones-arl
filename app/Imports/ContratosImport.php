<?php

namespace App\Imports;

use App\Models\Contrato;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContratosImport
{
    public int $created = 0;
    public int $updated = 0;
    public array $errors = [];

    private string $vigencia;

    public function __construct(string $vigencia)
    {
        $this->vigencia = $vigencia;
    }

    public function processWorksheet(Worksheet $worksheet): void
    {
        $sheet = new ContratoSheetImport($this->vigencia, $this);
        $sheet->processWorksheet($worksheet);
    }
}

class ContratoSheetImport
{
    private string $vigencia;
    private ContratosImport $parent;

    public function __construct(string $vigencia, ContratosImport $parent)
    {
        $this->vigencia = $vigencia;
        $this->parent   = $parent;
    }

    /**
     * Procesa la hoja directamente desde PhpSpreadsheet.
     * Fila 1 = cabeceras, Fila 2 = índice numérico (se salta), Fila 3+ = datos.
     * Se indexa por LETRA DE COLUMNA para evitar colisiones con cabeceras duplicadas.
     */
    public function processWorksheet(Worksheet $worksheet): void
    {
        foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
            if ($rowIndex === 1 || $rowIndex === 2) {
                continue; // Fila 1 = cabeceras (no las necesitamos), Fila 2 = índice numérico
            }

            // Leer valores indexados por letra de columna: ['A' => valor, 'B' => valor, ...]
            $cells = [];
            $cellIter = $row->getCellIterator();
            $cellIter->setIterateOnlyExistingCells(true); // Solo celdas con contenido
            foreach ($cellIter as $col => $cell) {
                try {
                    $cells[$col] = $cell->getCalculatedValue(); // Evalúa fórmulas
                } catch (\Throwable) {
                    $cells[$col] = $cell->getValue(); // Fallback: valor crudo
                }
            }

            // Saltar filas completamente vacías
            if (empty(array_filter($cells, fn($v) => $v !== null && $v !== ''))) {
                continue;
            }

            $data = [];
            try {
                $data = $this->mapRow($cells);

                // Saltar filas sin identificador válido (evita crear registros en blanco)
                if (empty($data['id_contrato_secop']) && empty($data['numero_constancia_secop'])) {
                    continue;
                }

                if (empty($data)) {
                    continue;
                }

                $data['vigencia'] = $this->vigencia;

                // Key 1: id_contrato_secop
                $contrato = null;
                $idSecop  = $data['id_contrato_secop'] ?? null;
                if ($idSecop) {
                    $contrato = Contrato::withTrashed()
                        ->where('id_contrato_secop', $idSecop)
                        ->first();
                }

                // Key 2 (fallback): numero_constancia_secop + vigencia
                if (! $contrato) {
                    $constancia = $data['numero_constancia_secop'] ?? null;
                    if ($constancia) {
                        $contrato = Contrato::withTrashed()
                            ->where('numero_constancia_secop', $constancia)
                            ->where('vigencia', $this->vigencia)
                            ->first();
                    }
                }

                if ($contrato) {
                    if ($contrato->trashed()) {
                        $contrato->restore();
                    }
                    $contrato->update($data);
                    $this->parent->updated++;
                } else {
                    Contrato::create($data);
                    $this->parent->created++;
                }
            } catch (\Throwable $e) {
                $this->parent->errors[] = [
                    'fila'                    => $rowIndex,
                    'id_contrato_secop'       => $data['id_contrato_secop']       ?? $cells['L'] ?? null,
                    'numero_constancia_secop' => $data['numero_constancia_secop'] ?? $cells['I'] ?? null,
                    'contratista'             => $data['nombre_persona_natural']  ?? $data['nombre_persona_juridica'] ?? $cells['DK'] ?? $cells['EC'] ?? null,
                    'objeto'                  => mb_substr($data['objeto'] ?? $cells['V'] ?? '', 0, 80),
                    'error_original'          => $e->getMessage(),
                ];
            }
        }
    }

    /**
     * Mapea las celdas (indexadas por letra de columna) a los campos del modelo.
     *
     * Columnas del Excel:
     * A=N°  B=CTO N°  E=TIPO  F=CONTRATISTA  H=FECHA ULT.MOD  I=Nº CONST SECOP
     * J=PROFESIONAL  K=ESTADO  L=ID CONTRATO SECOP  M=NIT ENTIDAD  N=ENTIDAD
     * O=UNIDAD EJECUCION  P=VIGENCIA  Q=CTO.INTERVENTORIA  R=NOMBRE INTERVENTOR
     * S=DIR INTERVENTOR  T=DOC INTERVENTOR  U=TIPO(2)  V=OBJETO
     * X=VALOR CONTRATO  Y=FECHA CONT  Z=FECHA APROBACION  AA=FECHA TERMINACION
     * AB=PLAZO AÑOS  AC=PLAZO MESES  AD=PLAZO DIAS
     * AF=VALOR ADIC1  AG=FECHA ADIC1  AH=PLAZO AÑOS ADIC1  AI=PLAZO MESES ADIC1  AJ=PLAZO DIAS ADIC1
     * AL=VALOR ADIC2  AM=FECHA ADIC2  AN=PLAZO AÑOS ADIC2  AO=PLAZO MESES ADIC2  AP=PLAZO DIAS ADIC2
     * AR=VALOR ADIC3  AS=FECHA ADIC3  AT=PLAZO AÑOS ADIC3  AU=PLAZO MESES ADIC3  AV=PLAZO DIAS ADIC3
     * BJ=SOLICITUD CDP  BK=FECHA SOLICITUD CDP  BL=Nº CDP  BM=FECHA CDP  BN=VALOR CDP
     * BR=SOLICITUD BPIN  BT=CODIGO BPIM  BU=CODIGO BPIN  BW=NOMBRE PROYECTO
     * BX=SECTOR  BY=PROGRAMA  BZ=SUBPROGRAMA  CA=DEPENDENCIA(BPIN)  CB=META PLAN DESARROLLO
     * CC=MODALIDAD  CD=MODALIDAD SELECCION  CM=CLASE  CN=TIPO CONTRATO  CP=MISIONAL APOYO
     * CS=ACTA AMPLIACION  CT=DIAS AMPLIACION  CU=TOTAL DIAS AMPLIACION
     * CV=NUM SUSPENSIONES  CW=DIAS SUSPENSION  CX=FECHA ACTA REINICIO
     * DD=CODIGO SEGMENTO  DE=SEGMENTO SERVICIO  DF=CODIGO UNSPSC  DG=DESC UNSPSC
     * DH=DIR BIEN INMUEBLE  DI=MATRICULA  DJ=CODIGO CATASTRAL
     * DK=PERSONA NATURAL  DL=GENERO  DM=CEDULA  DN=LUGAR EXP CEDULA  DO=FECHA EXP CEDULA
     * DP=LUGAR NACIMIENTO  DQ=FECHA NACIMIENTO  DS=TITULO BACHILLER  DT=AÑO BACHILLER
     * DU=TITULO PROFESIONAL  DV=UNIVERSIDAD  DW=PERFIL  DX=AÑO GRADO PROF
     * DY=ESPECIALIZACIONES  DZ=UNIVERSIDAD POSGRADO  EA=AÑO GRADO POSGRADO  EB=CORREO
     * EC=PERSONA JURIDICA  ED=NIT CONTRATISTA  EE=DV  EF=DIRECCION  EG=CIUDAD  EH=TELEFONO
     * EI=ENTIDAD BANCARIA  EJ=TIPO CUENTA  EK=Nº CUENTA
     * EL=INTEGRANTE1  EM=%PARTICIP1  EN=TIPO DOC1  EO=DOC1  EP=DIR1
     * EQ=INTEGRANTE2  ER=%PARTICIP2  ES=TIPO DOC2  ET=DOC2  EU=DIR2
     * EV=INTEGRANTE3  EW=%PARTICIP3  EX=TIPO DOC3  EY=DOC3  EZ=DIR3
     * FA=DEPENDENCIA(SUPERVISION)  FB=ASIGNADO SUPERVISION  FC=SUPERVISION1
     * FD=ID SUPERVISOR  FE=TITULO SUPERVISOR  FF=NOMBRE SUPERVISOR  FG=CARGO SUPERVISOR
     * FH=TIPO VINCULACION  FI=OFICINA  FJ=FECHA DESIGNACION
     * FK=ACTA POLIZA  FL=FECHA APROBACION POLIZA  FM=COMPANIA ASEGURADORA  FN=NIT ASEGURADORA
     * FO=POLIZA CUMPLIMIENTO  FP=ANEXO CUMPLIMIENTO  FQ=FECHA EXP POLIZA CUMPL
     * FR=VIGENCIA CUMPLIMIENTO  FS=VIGENCIA PAGO ANTICIPADO  FT=VIGENCIA PAGO SALARIOS
     * FU=VIGENCIA CALIDAD  FV=POLIZA RESPONSABILIDAD  FW=ANEXO RESPONSAB
     * FX=FECHA EXP POLIZA RESPONSAB  FY=VIGENCIA RESPONSABILIDAD
     * FZ=Nº CRP  GA=FECHA CRP  GB=VALOR CRP  GC=RECURSOS SGP  GD=RECURSOS SGR
     * GF=RECURSOS PGN  GG=OTROS RECURSOS  GJ=PRODUCTO MGA  GK=PRODUCTO CPC
     * GL=FUENTE RECURSOS RP  GM=FUENTE RECURSO  GN=FUENTE FINANCIACION
     * GO=CODIGO RUBRO  GP=NOMBRE RUBRO  GQ=VALOR RUBRO
     * GV=FECHA INICIO CONTRATO  GW=TIENE ANTICIPO  GX=TIPO ANTICIPO
     * GY=PORCENTAJE ANTICIPO  GZ=VALOR ANTICIPO  HA=FECHA ANTICIPO
     * HX=FECHA ACTA RECIBO FINAL  HY=FECHA ACTA LIQUIDACION  HZ=VALOR ACTA LIQUIDACION
     * IA=FECHA REVERSION SALDO  IB=VALOR REVERSION  ID=LINK  IE=RECURSOS REACTIVACION
     */
    private function mapRow(array $c): array
    {
        $data = [
            // Identificación
            'numero_contrato'           => $this->toInt($c['A'] ?? null),
            'fecha_ultima_modificacion' => $this->toDate($c['H'] ?? null),
            'numero_constancia_secop'   => $this->toStr($c['I'] ?? null),
            'profesional_encargado'     => $this->toStr($c['J'] ?? null),
            'estado'                    => $this->toStr($c['K'] ?? null),
            'id_contrato_secop'         => $this->toStr($c['L'] ?? null),
            'nit_entidad'               => $this->toStr($c['M'] ?? null),
            'entidad'                   => $this->toStr($c['N'] ?? null),
            'unidad_ejecucion'          => $this->toStr($c['O'] ?? null),
            'vigencia'                  => $this->toStr($c['P'] ?? null),
            'tipo'                      => $this->toStr($c['E'] ?? null),

            // Interventoría
            'cto_interventoria'    => $this->toStr($c['Q'] ?? null),
            'nombre_interventor'   => $this->toStr($c['R'] ?? null),
            'direccion_interventor'=> $this->toStr($c['S'] ?? null),
            'documento_interventor'=> $this->toStr($c['T'] ?? null),

            // Objeto y fechas
            'objeto'            => $this->toStr($c['V'] ?? null),
            'valor_contrato'    => $this->toDecimal($c['X'] ?? null),
            'fecha_contrato'    => $this->toDate($c['Y'] ?? null),
            'fecha_aprobacion'  => $this->toDate($c['Z'] ?? null),
            'fecha_terminacion' => $this->toDate($c['AA'] ?? null),
            'plazo_anos'        => $this->toInt($c['AB'] ?? null),
            'plazo_meses'       => $this->toInt($c['AC'] ?? null),
            'plazo_dias'        => $this->toInt($c['AD'] ?? null),

            // Adición 1
            'valor_adicional_1'      => $this->toDecimal($c['AF'] ?? null),
            'fecha_adicional_1'       => $this->toDate($c['AG'] ?? null),
            'plazo_anos_adicional_1'  => $this->toInt($c['AH'] ?? null),
            'plazo_meses_adicional_1' => $this->toInt($c['AI'] ?? null),
            'plazo_dias_adicional_1'  => $this->toInt($c['AJ'] ?? null),

            // Adición 2
            'valor_adicional_2'      => $this->toDecimal($c['AL'] ?? null),
            'fecha_adicional_2'       => $this->toDate($c['AM'] ?? null),
            'plazo_anos_adicional_2'  => $this->toInt($c['AN'] ?? null),
            'plazo_meses_adicional_2' => $this->toInt($c['AO'] ?? null),
            'plazo_dias_adicional_2'  => $this->toInt($c['AP'] ?? null),

            // Adición 3
            'valor_adicional_3'      => $this->toDecimal($c['AR'] ?? null),
            'fecha_adicional_3'       => $this->toDate($c['AS'] ?? null),
            'plazo_anos_adicional_3'  => $this->toInt($c['AT'] ?? null),
            'plazo_meses_adicional_3' => $this->toInt($c['AU'] ?? null),
            'plazo_dias_adicional_3'  => $this->toInt($c['AV'] ?? null),

            // CDP
            'solicitud_cdp'       => $this->toStr($c['BJ'] ?? null),
            'fecha_solicitud_cdp' => $this->toDate($c['BK'] ?? null),
            'numero_cdp'          => $this->toStr($c['BL'] ?? null),
            'fecha_cdp'           => $this->toDate($c['BM'] ?? null),
            'valor_cdp'           => $this->toDecimal($c['BN'] ?? null),

            // BPIN
            'solicitud_bpin'       => $this->toStr($c['BR'] ?? null),
            'codigo_bpim'          => $this->toStr($c['BT'] ?? null),
            'codigo_bpin'          => $this->toStr($c['BU'] ?? null),
            'nombre_proyecto'      => $this->toStr($c['BW'] ?? null),
            'sector'               => $this->toStr($c['BX'] ?? null),
            'programa'             => $this->toStr($c['BY'] ?? null),
            'subprograma'          => $this->toStr($c['BZ'] ?? null),
            'dependencia_proyecto' => $this->toStr($c['CA'] ?? null),
            'meta_plan_desarrollo' => $this->toStr($c['CB'] ?? null),

            // Modalidad / tipo contrato
            'modalidad'          => $this->toStr($c['CC'] ?? null),
            'modalidad_seleccion'=> $this->toStr($c['CD'] ?? null),
            'clase'              => $this->toStr($c['CM'] ?? null),
            'tipo_contrato'      => $this->toStr($c['CN'] ?? null),
            'misional_apoyo'     => $this->toStr($c['CP'] ?? null),

            // Ampliaciones / suspensiones
            'acta_ampliacion_plazo' => $this->toStr($c['CS'] ?? null),
            'dias_ampliacion'       => $this->toInt($c['CT'] ?? null),
            'total_dias_ampliacion' => $this->toInt($c['CU'] ?? null),
            'numero_suspensiones'   => $this->toInt($c['CV'] ?? null),
            'dias_suspension'       => $this->toInt($c['CW'] ?? null),
            'fecha_acta_reinicio'   => $this->toDate($c['CX'] ?? null),

            // UNSPSC / Bien inmueble
            'segmento_servicio'  => $this->toStr($c['DE'] ?? null),
            'codigo_unspsc'      => $this->toStr($c['DF'] ?? null),
            'descripcion_unspsc' => $this->toStr($c['DG'] ?? null),
            'direccion_bien_inmueble' => $this->toStr($c['DH'] ?? null),
            'matricula'          => $this->toStr($c['DI'] ?? null),
            'codigo_catastral'   => $this->toStr($c['DJ'] ?? null),

            // Persona natural
            'nombre_persona_natural'  => $this->toStr($c['DK'] ?? null),
            'genero'                  => $this->toStr($c['DL'] ?? null),
            'cedula'                  => $this->toStr($c['DM'] ?? null),
            'lugar_expedicion_cedula' => $this->toStr($c['DN'] ?? null),
            'fecha_expedicion_cedula' => $this->toDate($c['DO'] ?? null),
            'lugar_nacimiento'        => $this->toStr($c['DP'] ?? null),
            'fecha_nacimiento'        => $this->toDate($c['DQ'] ?? null),
            'titulo_bachiller'        => $this->toStr($c['DS'] ?? null),
            'ano_bachiller'           => $this->toInt($c['DT'] ?? null),
            'titulo_profesional'      => $this->toStr($c['DU'] ?? null),
            'universidad'             => $this->toStr($c['DV'] ?? null),
            'perfil'                  => $this->toStr($c['DW'] ?? null),
            'ano_grado_profesional'   => $this->toInt($c['DX'] ?? null),
            'especializaciones'       => $this->toStr($c['DY'] ?? null),
            'universidad_posgrado'    => $this->toStr($c['DZ'] ?? null),
            'ano_grado_posgrado'      => $this->toInt($c['EA'] ?? null),
            'correo_contratista'      => $this->toStr($c['EB'] ?? null),

            // Persona jurídica
            'nombre_persona_juridica' => $this->toStr($c['EC'] ?? null),
            'nit_contratista'         => $this->toStr($c['ED'] ?? null),
            'dv'                      => $this->toInt($c['EE'] ?? null),
            'direccion_contratista'   => $this->toStr($c['EF'] ?? null),
            'ciudad_contratista'      => $this->toStr($c['EG'] ?? null),
            'telefono_contratista'    => $this->toStr($c['EH'] ?? null),
            'entidad_bancaria'        => $this->toStr($c['EI'] ?? null),
            'tipo_cuenta_bancaria'    => $this->toStr($c['EJ'] ?? null),
            'numero_cuenta_bancaria'  => $this->toStr($c['EK'] ?? null),

            // Consorcio / UT
            'integrante_1_consorcio' => $this->toStr($c['EL'] ?? null),
            'participacion_1'        => $this->toStr($c['EM'] ?? null),
            'tipo_doc_integrante_1'  => $this->toStr($c['EN'] ?? null),
            'doc_integrante_1'       => $this->toStr($c['EO'] ?? null),
            'direccion_integrante_1' => $this->toStr($c['EP'] ?? null),
            'integrante_2_consorcio' => $this->toStr($c['EQ'] ?? null),
            'participacion_2'        => $this->toStr($c['ER'] ?? null),
            'tipo_doc_integrante_2'  => $this->toStr($c['ES'] ?? null),
            'doc_integrante_2'       => $this->toStr($c['ET'] ?? null),
            'direccion_integrante_2' => $this->toStr($c['EU'] ?? null),
            'integrante_3_consorcio' => $this->toStr($c['EV'] ?? null),
            'participacion_3'        => $this->toStr($c['EW'] ?? null),
            'tipo_doc_integrante_3'  => $this->toStr($c['EX'] ?? null),
            'doc_integrante_3'       => $this->toStr($c['EY'] ?? null),
            'direccion_integrante_3' => $this->toStr($c['EZ'] ?? null),

            // Supervisión
            'dependencia_contrato'          => $this->toStr($c['FA'] ?? null),
            'asignado_supervision'          => $this->toStr($c['FB'] ?? null),
            'tipo_supervision'              => $this->toStr($c['FC'] ?? null),
            'identificacion_supervisor'     => $this->toStr($c['FD'] ?? null),
            'titulo_supervisor'             => $this->toStr($c['FE'] ?? null),
            'nombre_supervisor'             => $this->toStr($c['FF'] ?? null),
            'cargo_supervisor'              => $this->toStr($c['FG'] ?? null),
            'tipo_vinculacion_supervisor'   => $this->toStr($c['FH'] ?? null),
            'oficina_supervisor'            => $this->toStr($c['FI'] ?? null),
            'fecha_designacion_supervision' => $this->toDate($c['FJ'] ?? null),

            // Pólizas
            'acta_aprobacion_poliza'                 => $this->toStr($c['FK'] ?? null),
            'fecha_aprobacion_poliza'                 => $this->toDate($c['FL'] ?? null),
            'compannia_aseguradora'                   => $this->toStr($c['FM'] ?? null),
            'nit_aseguradora'                         => $this->toStr($c['FN'] ?? null),
            'poliza_cumplimiento'                     => $this->toStr($c['FO'] ?? null),
            'anexo_cumplimiento'                      => $this->toStr($c['FP'] ?? null),
            'fecha_expedicion_poliza_cumplimiento'    => $this->toDate($c['FQ'] ?? null),
            'vigencia_cumplimiento'                   => $this->toStr($c['FR'] ?? null),
            'vigencia_pago_anticipado'                => $this->toStr($c['FS'] ?? null),
            'vigencia_pago_salarios'                  => $this->toStr($c['FT'] ?? null),
            'vigencia_calidad_servicio'               => $this->toStr($c['FU'] ?? null),
            'poliza_responsabilidad'                  => $this->toStr($c['FV'] ?? null),
            'anexo_responsabilidad'                   => $this->toStr($c['FW'] ?? null),
            'fecha_expedicion_poliza_responsabilidad' => $this->toDate($c['FX'] ?? null),
            'vigencia_responsabilidad'                => $this->toStr($c['FY'] ?? null),

            // CRP / Recursos
            'numero_crp'          => $this->toStr($c['FZ'] ?? null),
            'fecha_crp'           => $this->toDate($c['GA'] ?? null),
            'valor_crp'           => $this->toDecimal($c['GB'] ?? null),
            'recursos_sgp'        => $this->toDecimal($c['GC'] ?? null),
            'recursos_sgr'        => $this->toDecimal($c['GD'] ?? null),
            'recursos_pgn'        => $this->toDecimal($c['GF'] ?? null),
            'otros_recursos'      => $this->toDecimal($c['GG'] ?? null),
            'producto_mga'        => $this->toStr($c['GJ'] ?? null),
            'producto_cpc'        => $this->toStr($c['GK'] ?? null),
            'fuente_recursos_rp'  => $this->toStr($c['GL'] ?? null),
            'fuente_recurso'      => $this->toStr($c['GM'] ?? null),
            'fuente_financiacion' => $this->toStr($c['GN'] ?? null),
            'codigo_rubro'        => $this->toStr($c['GO'] ?? null),
            'nombre_rubro'        => $this->toStr($c['GP'] ?? null),
            'valor_rubro'         => $this->toDecimal($c['GQ'] ?? null),

            // Fechas de inicio y anticipo
            'fecha_inicio'        => $this->toDate($c['GV'] ?? null),
            'tiene_anticipo'      => $this->toBool($c['GW'] ?? null),
            'tipo_anticipo'       => $this->toStr($c['GX'] ?? null),
            'porcentaje_anticipo' => $this->toDecimal($c['GY'] ?? null),
            'valor_anticipo'      => $this->toDecimal($c['GZ'] ?? null),
            'fecha_anticipo'      => $this->toDate($c['HA'] ?? null),

            // Liquidación
            'fecha_acta_recibo_final' => $this->toDate($c['HX'] ?? null),
            'fecha_acta_liquidacion'  => $this->toDate($c['HY'] ?? null),
            'valor_acta_liquidacion'  => $this->toDecimal($c['HZ'] ?? null),
            'fecha_reversion_saldo'   => $this->toDate($c['IA'] ?? null),
            'valor_reversion'         => $this->toDecimal($c['IB'] ?? null),

            // Extra
            'link_secop'            => $this->toStr($c['ID'] ?? null),
            'recursos_reactivacion' => $this->toStr($c['IE'] ?? null),
        ];

        // Eliminar nulos para no pisar campos ya guardados
        return array_filter($data, fn ($v) => $v !== null);
    }

    private function toStr(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $str = trim((string) $value);
        if (str_starts_with($str, '=')) {
            return null; // Descartar fórmulas cacheadas como texto
        }

        return $str === '' ? null : $str;
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        return null;
    }

    private function toDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $clean = preg_replace('/[^\d,.-]/', '', (string) $value);
        if (preg_match('/,\d{1,2}$/', $clean)) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(['.', ','], ['', '.'], $clean);
        }

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);

                return Carbon::instance($date)->toDateString();
            }
            if (is_string($value)) {
                $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y', 'Y/m/d'];
                foreach ($formats as $fmt) {
                    try {
                        return Carbon::createFromFormat($fmt, trim($value))->toDateString();
                    } catch (\Exception) {
                        // intentar siguiente formato
                    }
                }
            }
        } catch (\Throwable) {
            // ignorar
        }

        return null;
    }

    private function toBool(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_bool($value)) {
            return $value;
        }
        $str = mb_strtolower(trim((string) $value));

        return in_array($str, ['1', 'si', 'sí', 'yes', 'true', 'x'], true);
    }
}
