<?php

namespace App\Services;

use App\Models\Afiliacion;
use App\Models\Contrato;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class AIReportService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function consultar(string $pregunta, array $historialPrevio = []): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GEMINI_API_KEY no está configurada en el servidor.'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Construir historial de conversación previo
        $contents = [];
        foreach ($historialPrevio as $msg) {
            if ($msg['rol'] === 'user') {
                $contents[] = ['role' => 'user', 'parts' => [['text' => $msg['texto']]]];
            } elseif ($msg['rol'] === 'ia' && ! empty($msg['texto'])) {
                $contents[] = ['role' => 'model', 'parts' => [['text' => $msg['texto']]]];
            }
        }
        // Agregar la pregunta actual
        $contents[] = ['role' => 'user', 'parts' => [['text' => $pregunta]]];

        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
            'tools'              => [['function_declarations' => $this->tools()]],
            'contents'           => $contents,
        ];

        // ─── Agentic loop: hasta 6 rondas de herramientas ────────────────────
        $maxRondas = 6;
        for ($ronda = 0; $ronda < $maxRondas; $ronda++) {
            $res = $this->postWithRetry($url, $payload);

            if (! $res->successful()) {
                return ['error' => 'Error al contactar Gemini: ' . $res->body()];
            }

            $data = $res->json();

            // Normalizar args:{} → stdClass (PHP json_decode convierte {} a [])
            $parts = array_map(function ($p) {
                if (isset($p['functionCall']['args']) && $p['functionCall']['args'] === []) {
                    $p['functionCall']['args'] = new \stdClass();
                }
                return $p;
            }, $data['candidates'][0]['content']['parts'] ?? []);

            $functionCalls = array_values(array_filter($parts, fn ($p) => isset($p['functionCall'])));

            // Sin function calls → respuesta final de texto
            if (empty($functionCalls)) {
                break;
            }

            // Agregar respuesta del modelo al historial
            $contents[] = ['role' => 'model', 'parts' => $parts];

            // Ejecutar todas las herramientas solicitadas
            $toolResponses = [];
            foreach ($functionCalls as $part) {
                $fn     = $part['functionCall'];
                $result = $this->ejecutar($fn['name'], (array) ($fn['args'] ?? []));

                $toolResponses[] = [
                    'functionResponse' => [
                        'name'     => $fn['name'],
                        'response' => ['result' => $result],
                    ],
                ];
            }

            $contents[] = ['role' => 'user', 'parts' => $toolResponses];
            $payload['contents'] = $contents;
        }

        // Extraer texto final
        $texto = collect($data['candidates'][0]['content']['parts'] ?? [])
            ->filter(fn ($p) => isset($p['text']))
            ->pluck('text')
            ->implode('');

        if (empty(trim($texto))) {
            return ['error' => 'Gemini no devolvió texto. Respuesta completa: ' . json_encode($data)];
        }

        return ['respuesta' => $texto];
    }

    // ─── HTTP helper con reintentos para 503 ──────────────────────────────────

    private function postWithRetry(string $url, array $payload, int $maxAttempts = 3): \Illuminate\Http\Client\Response
    {
        $attempt = 0;
        do {
            $attempt++;
            $res = Http::timeout(60)->post($url, $payload);
            if ($res->successful() || $res->status() !== 503) {
                return $res;
            }
            if ($attempt < $maxAttempts) {
                sleep($attempt * 2);
            }
        } while ($attempt < $maxAttempts);

        return $res;
    }

    // ─── System prompt ────────────────────────────────────────────────────────

    private function systemPrompt(): string
    {
        $hoy        = now()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
        $anioActual = now()->year;
        $trimestre  = (int) ceil(now()->month / 3);

        // Dependencias reales de la BD
        $dependencias = DB::table('dependencias')->orderBy('nombre')->pluck('nombre')->implode(', ');

        // Vigencias disponibles en contratos
        $vigencias = DB::table('contratos')->distinct()->orderByDesc('vigencia')->pluck('vigencia')->implode(', ');

        // Fuentes de financiación
        $fuentes = DB::table('contratos')
            ->whereNotNull('fuente_financiacion')
            ->distinct()->pluck('fuente_financiacion')->implode(', ');

        return "Eres un asistente de análisis de datos del Sistema de Gestión ARL de la Alcaldía Municipal de Puerto Boyacá. " .
               "Tienes acceso a información real sobre contratos SECOP y afiliaciones ARL mediante herramientas. " .
               "SIEMPRE usa las herramientas disponibles para consultar datos antes de responder. " .
               "NUNCA digas que no tienes acceso a información sin intentar consultar la herramienta adecuada primero. " .
               "Si necesitas varios datos, llama varias herramientas en la misma respuesta o en rondas sucesivas. " .
               "NUNCA hagas preguntas de seguimiento al usuario para aclarar cómo quiere los datos: SIEMPRE responde directamente con la información completa. " .
               "Si la pregunta pide datos por varios criterios (ej: por tipo Y por fuente), consulta los datos y presenta AMBOS en la respuesta. " .
               "Responde siempre en español, de forma clara, concisa y profesional. " .
               "Cuando presentes listas usa formato estructurado con viñetas o numeración. " .
               "Si la pregunta no está relacionada con contratos o afiliaciones, indícalo amablemente.\n\n" .

               "CONTEXTO DEL DÍA:\n" .
               "- Fecha actual: {$hoy}\n" .
               "- Año actual: {$anioActual}\n" .
               "- Trimestre actual: {$trimestre} (T{$trimestre} de {$anioActual})\n\n" .

               "ESTRUCTURA DE LA BASE DE DATOS:\n" .
               "- Dependencias registradas: {$dependencias}\n" .
               "- Vigencias disponibles en contratos: {$vigencias}\n" .
               "- Fuentes de financiación: {$fuentes}\n\n" .

               "TIPOS DE CONTRATO:\n" .
               "- La agrupación por_tipo_contrato en contratos_detallado ya desglosa automáticamente los contratos:\n" .
               "  * 'C1 Prestación de Servicios Profesionales' → servicios profesionales\n" .
               "  * 'C2 Prestación de Servicios de Apoyo a la Gestión' → apoyo a la gestión / técnico / tecnológico\n" .
               "  * 'C9 ARRENDAMIENTOS' → arrendamientos de bienes inmuebles\n" .
               "  * 'C12 INTERADMINISTRATIVOS' → convenios interadministrativos\n" .
               "  * 'C14 INTERVENTORIAS' → contratos de interventoría\n" .
               "  * 'C1 PRESTACION DE SERVICIOS' (sin tipo_contrato) → prestación de servicios sin clasificar\n" .
               "- NUNCA uses 'NO APLICA' en tu respuesta: usa siempre el nombre de la clase real (Arrendamientos, Interventorías, etc.).\n" .
               "- El campo 'modalidad' contiene SOLO códigos (CD-CPS, LIC-006, etc.), NO descripciones de tipo.\n\n" .

               "ESTADOS DE AFILIACIÓN: pendiente, validado, rechazado\n" .
               "ESTADOS DE CONTRATO SECOP: EN EJECUCION, EN EJECUCION CON ADICION, TERMINADO\n\n" .

               "NOTA SOBRE DEPENDENCIAS EN CONTRATOS SECOP:\n" .
               "- Algunos contratos (especialmente NO APLICA: arrendamientos, interadministrativos, interventorías, licitaciones)\n" .
               "  NO tienen dependencia_id asignada en la BD; usan el campo texto 'dependencia_contrato'.\n" .
               "- Esos contratos pertenecen a 'D07 Administración Central' según el campo texto.\n" .
               "- Si aparece 'Sin dependencia' en agrupación, corresponde a estos contratos de Administración Central.\n\n" .

               "CLASES DE CONTRATO (campo 'clase'):\n" .
               "C1 PRESTACION DE SERVICIOS, C1 PRESTACIONES DE SERVICIOS, C4 OBRA PUBLICA, " .
               "C6 SUMINISTROS, C9 ARRENDAMIENTOS, C12 INTERADMINISTRATIVOS, C14 INTERVENTORIAS\n\n" .

               "FUENTES DE FINANCIACIÓN: FUNCIONAMIENTO, REGALIAS, INVERSION\n" .
               "ARL disponible: ARL SURA | EPS disponible: NUEVA EPS\n\n" .

               "HERRAMIENTAS DISPONIBLES (elige siempre la más específica):\n" .
               "CONTEXTO: listar_dependencias, listar_areas, estadisticas_generales\n" .
               "CONTRATOS: resumen_contratos, contratos_por_dependencia, contratos_detallado, contratos_vigentes_hoy, " .
               "contratos_proximos_vencer, contratos_vencidos, contratos_con_prorroga, contratos_con_adicion, " .
               "contratos_con_anticipo, contratos_por_supervisor, contratos_por_objeto, contratos_por_clase, " .
               "contratos_liquidados, presupuesto_por_fuente, top_contratistas, contratistas_multivigencia\n" .
               "AFILIACIONES: resumen_afiliaciones, afiliaciones_detallado, afiliaciones_por_dependencia, " .
               "afiliaciones_por_eps, afiliaciones_proximas_vencer, afiliaciones_vencidas, afiliaciones_con_novedad, " .
               "afiliaciones_rechazadas, estadisticas_afiliaciones, top_contratistas_afiliaciones\n" .
               "BÚSQUEDA: buscar_contratista, buscar_contrato, buscar_por_documento\n" .
               "CRUCE: cruce_contrato_afiliacion\n" .
               "EXPORTAR: exportar_excel (para listas largas o cuando pidan 'archivo', 'Excel', 'plano')\n\n" .

               "REGLAS DE USO DE HERRAMIENTAS:\n" .
               "- Para preguntas sobre un trimestre específico, usa el parámetro trimestre (1=ene-mar, 2=abr-jun, 3=jul-sep, 4=oct-dic).\n" .
               "- Para el año actual usa vigencia={$anioActual}.\n" .
               "- Para listar dependencias disponibles usa la herramienta listar_dependencias.\n" .
               "- Para detalle de afiliaciones (por dependencia, año, trimestre, ARL) usa afiliaciones_detallado.\n" .
               "- Para detalle de contratos SECOP con filtros múltiples usa contratos_detallado.\n" .
               "- Cuando el usuario pide 'más detalle', 'detalle de cada contrato', 'listado', 'uno a uno' o 'qué contratos son':\n" .
               "  USA SIEMPRE con_detalle: true en contratos_detallado para obtener el listado individual.\n" .
               "- El campo con_detalle devuelve número, contratista, clase, objeto, valor, fechas, fuente y dependencia de cada contrato.\n" .
               "- Para saber qué contratos están activos HOY usa contratos_vigentes_hoy.\n" .
               "- Para buscar por número de documento (cédula/NIT) usa buscar_por_documento.\n" .
               "- Para verificar cobertura ARL de contratos usa cruce_contrato_afiliacion.";
    }

    // ─── Definición de herramientas (formato Gemini) ──────────────────────────

    private function tools(): array
    {
        return [
            // ── CONTEXTO ───────────────────────────────────────────────────
            [
                'name'        => 'listar_dependencias',
                'description' => 'Devuelve la lista completa de dependencias registradas en el sistema con sus IDs. ' .
                                 'Usa esta herramienta primero si necesitas filtrar por dependencia.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // ── CONTRATOS SECOP ────────────────────────────────────────────
            [
                'name'        => 'resumen_contratos',
                'description' => 'Resumen general de contratos SECOP: total, valor total y desglose por estado. ' .
                                 'Acepta filtro por vigencia (año) y/o dependencia.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Ej: "2026". Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_por_dependencia',
                'description' => 'Cantidad y valor total de contratos SECOP agrupados por dependencia.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia' => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'estado'   => ['type' => 'string', 'description' => 'Estado del contrato. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_detallado',
                'description' => 'Consulta detallada de contratos SECOP con múltiples filtros. ' .
                                 'IMPORTANTE: Para filtrar por tipo de contrato usa tipo_contrato (texto descriptivo), NO modalidad (que son códigos). ' .
                                 'Siempre devuelve agrupación por tipo_contrato, por fuente_financiacion y por dependencia simultáneamente. ' .
                                 'Incluye prórrogas y adiciones.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Ej: "2026". Opcional.'],
                        'tipo_contrato'      => ['type' => 'string',  'description' => 'Texto parcial del tipo. Ej: "Apoyo a la Gestión", "Servicios Profesionales". LIKE search. Opcional.'],
                        'tipos_contrato'     => ['type' => 'array',   'items' => ['type' => 'string'],
                                                 'description' => 'Lista de tipos de contrato (OR). Ej: ["Apoyo a la Gestión","Servicios Profesionales"]. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'modalidad'          => ['type' => 'string',  'description' => 'Código de modalidad (NO texto descriptivo). Ej: "CD-CPS". Opcional.'],
                        'trimestre'          => ['type' => 'integer', 'description' => 'Trimestre por fecha_inicio: 1=ene-mar, 2=abr-jun, 3=jul-sep, 4=oct-dic. Opcional.'],
                        'fecha_desde'        => ['type' => 'string',  'description' => 'Fecha inicio rango (YYYY-MM-DD). Alternativa a trimestre. Opcional.'],
                        'fecha_hasta'        => ['type' => 'string',  'description' => 'Fecha fin rango (YYYY-MM-DD). Alternativa a trimestre. Opcional.'],
                        'agrupar_por'        => ['type' => 'string',  'description' => 'Agrupar por: "tipo_contrato", "fuente_financiacion", "dependencia". Por defecto "tipo_contrato".'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true devuelve listado individual (máx 50). Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_proximos_vencer',
                'description' => 'Lista contratos SECOP activos que vencen en los próximos N días (considera adiciones y prórrogas).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dias' => ['type' => 'integer', 'description' => 'Días de anticipación. Por defecto 30.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_vencidos',
                'description' => 'Contratos SECOP en estado TERMINADO, con filtros opcionales.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'top_contratistas',
                'description' => 'Contratistas SECOP con más contratos registrados.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'limite'   => ['type' => 'integer', 'description' => 'Cantidad de resultados. Por defecto 10.'],
                        'vigencia' => ['type' => 'string',  'description' => 'Año. Opcional.'],
                    ],
                ],
            ],

            // ── AFILIACIONES ARL ───────────────────────────────────────────
            [
                'name'        => 'resumen_afiliaciones',
                'description' => 'Totales de afiliaciones ARL por estado. Filtros opcionales por dependencia y año.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'anio'               => ['type' => 'integer', 'description' => 'Año de fecha_inicio. Ej: 2026. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_detallado',
                'description' => 'Consulta detallada de afiliaciones ARL con múltiples filtros: estado, dependencia, año, trimestre, ARL, tipo de riesgo. ' .
                                 'Devuelve resumen y opcionalmente listado individual.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'estado'             => ['type' => 'string',  'description' => 'Estado: pendiente, validado, rechazado. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'anio'               => ['type' => 'integer', 'description' => 'Año de fecha_inicio. Opcional.'],
                        'trimestre'          => ['type' => 'integer', 'description' => 'Trimestre: 1=ene-mar, 2=abr-jun, 3=jul-sep, 4=oct-dic. Opcional.'],
                        'nombre_arl'         => ['type' => 'string',  'description' => 'Nombre parcial de la ARL. Ej: "SURA". Opcional.'],
                        'tipo_riesgo'        => ['type' => 'string',  'description' => 'Tipo de riesgo. Opcional.'],
                        'agrupar_por'        => ['type' => 'string',  'description' => 'Agrupar por: "dependencia", "estado", "arl", "tipo_riesgo". Por defecto "dependencia".'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true devuelve listado individual (máx 50). Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_proximas_vencer',
                'description' => 'Afiliaciones ARL validadas cuyo contrato vence en los próximos N días.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dias' => ['type' => 'integer', 'description' => 'Días de anticipación. Por defecto 30.'],
                    ],
                ],
            ],
            [
                'name'        => 'top_contratistas_afiliaciones',
                'description' => 'Contratistas con más afiliaciones ARL registradas.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'limite' => ['type' => 'integer', 'description' => 'Cantidad de resultados. Por defecto 10.'],
                        'estado' => ['type' => 'string',  'description' => 'Filtrar por estado de afiliación. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_por_dependencia',
                'description' => 'Afiliaciones ARL agrupadas por dependencia con cantidades y valor de contratos.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'estado' => ['type' => 'string',  'description' => 'Filtrar por estado. Opcional.'],
                        'anio'   => ['type' => 'integer', 'description' => 'Año de fecha_inicio. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'estadisticas_afiliaciones',
                'description' => 'Estadísticas de afiliaciones agrupadas por ARL, tipo de riesgo, EPS o estado.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'agrupar_por' => ['type' => 'string', 'description' => 'Campo: "arl", "tipo_riesgo", "eps", "estado". Por defecto "arl".'],
                        'anio'        => ['type' => 'integer', 'description' => 'Año. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'exportar_excel',
                'description' => 'Genera un archivo Excel (.xlsx) descargable con listado completo de contratos o afiliaciones. ' .
                                 'USA ESTA HERRAMIENTA cuando el usuario pida "exportar", "descargar", "Excel", "archivo plano", ' .
                                 '"archivo magnético", "listado completo" o cuando el listado tenga más de 20 registros. ' .
                                 'Devuelve una URL de descarga directa.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'tipo'               => ['type' => 'string',  'description' => '"contratos" o "afiliaciones". Por defecto "contratos".'],
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Ej: "2026". Opcional.'],
                        'tipo_contrato'      => ['type' => 'string',  'description' => 'Texto parcial del tipo de contrato. Opcional.'],
                        'tipos_contrato'     => ['type' => 'array',   'items' => ['type' => 'string'], 'description' => 'Lista de tipos de contrato. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'trimestre'          => ['type' => 'integer', 'description' => 'Trimestre: 1-4. Opcional.'],
                        'fecha_desde'        => ['type' => 'string',  'description' => 'Fecha inicio YYYY-MM-DD. Opcional.'],
                        'fecha_hasta'        => ['type' => 'string',  'description' => 'Fecha fin YYYY-MM-DD. Opcional.'],
                        'estado'             => ['type' => 'string',  'description' => 'Estado (para afiliaciones). Opcional.'],
                        'nombre_archivo'     => ['type' => 'string',  'description' => 'Nombre descriptivo del archivo sin extensión. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'buscar_contratista',
                'description' => 'Busca un contratista por nombre (una o varias palabras) y retorna sus contratos SECOP y afiliaciones ARL. ' .
                                 'Divide el nombre en palabras para buscar aunque el nombre completo tenga más palabras intermedias.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'nombre'  => ['type' => 'string',  'description' => 'Nombre parcial o completo del contratista. Puede ser solo apellido, solo nombre, o combinación.'],
                        'limite'  => ['type' => 'integer', 'description' => 'Máximo resultados por entidad. Por defecto 10.'],
                    ],
                    'required' => ['nombre'],
                ],
            ],
            [
                'name'        => 'buscar_contrato',
                'description' => 'Busca un contrato específico por su número y/o vigencia. Usa esta herramienta cuando el usuario mencione un número de contrato.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'numero'   => ['type' => 'integer', 'description' => 'Número del contrato. Requerido.'],
                        'vigencia' => ['type' => 'integer', 'description' => 'Vigencia (año) del contrato. Opcional.'],
                    ],
                    'required' => ['numero'],
                ],
            ],

            // ── CONTEXTO ADICIONAL ─────────────────────────────────────────
            [
                'name'        => 'listar_areas',
                'description' => 'Devuelve todas las áreas registradas con su dependencia y código. Útil para filtrar por área.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],
            [
                'name'        => 'estadisticas_generales',
                'description' => 'Estadísticas globales del sistema: totales de contratos, afiliaciones, contratistas únicos, ' .
                                 'valor total contratado, vigencias disponibles. Resumen ejecutivo completo.',
                'parameters'  => ['type' => 'object', 'properties' => new \stdClass()],
            ],

            // ── CONTRATOS AVANZADO ─────────────────────────────────────────
            [
                'name'        => 'contratos_vigentes_hoy',
                'description' => 'Contratos cuya fecha_inicio ya pasó y fecha de terminación (considerando prórrogas y adiciones) aún no ha llegado. ' .
                                 'Representa los contratos ACTIVOS en este momento.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista los contratos uno a uno. Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_con_prorroga',
                'description' => 'Contratos SECOP que tienen una o más prórrogas (extensiones de plazo). ' .
                                 'Muestra fecha original vs nueva fecha y días/meses adicionales.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista contrato por contrato. Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_con_adicion',
                'description' => 'Contratos SECOP que tienen adiciones en valor. Muestra valor original, valor adicionado y total. ' .
                                 'Útil para ver qué contratos han incrementado su presupuesto.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista contrato por contrato. Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_con_anticipo',
                'description' => 'Contratos SECOP que tienen anticipo pactado. Muestra porcentaje, valor y tipo de anticipo.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_por_supervisor',
                'description' => 'Contratos SECOP agrupados por supervisor. Muestra cuántos contratos y qué valor supervisa cada persona.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'nombre_supervisor'  => ['type' => 'string',  'description' => 'Nombre parcial del supervisor para buscar contratos de una persona específica. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista los contratos de cada supervisor. Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'presupuesto_por_fuente',
                'description' => 'Valor total de contratos desglosado por fuente de financiación (FUNCIONAMIENTO, REGALIAS, INVERSION, etc.). ' .
                                 'También muestra los recursos específicos: SGP, SGR, PGN, otros.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_por_objeto',
                'description' => 'Busca contratos SECOP cuyo objeto/descripción contenga las palabras indicadas. ' .
                                 'Ideal para encontrar contratos por tema: "interventoría vía", "software", "mantenimiento".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'texto'              => ['type' => 'string',  'description' => 'Palabras a buscar en el objeto del contrato. Requerido.'],
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'limite'             => ['type' => 'integer', 'description' => 'Máximo resultados. Por defecto 15.'],
                    ],
                    'required' => ['texto'],
                ],
            ],
            [
                'name'        => 'contratistas_multivigencia',
                'description' => 'Contratistas que han tenido contratos en más de una vigencia. Muestra historial por contratista.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'minimo_vigencias' => ['type' => 'integer', 'description' => 'Mínimo de vigencias distintas. Por defecto 2.'],
                        'limite'           => ['type' => 'integer', 'description' => 'Máximo contratistas. Por defecto 20.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_liquidados',
                'description' => 'Contratos que ya tienen acta de liquidación. Muestra valor final, fecha y valor devuelto al municipio.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_por_clase',
                'description' => 'Contratos SECOP agrupados por clase contable (C1 PRESTACION DE SERVICIOS, C4 OBRA PUBLICA, C6 SUMINISTROS, ' .
                                 'C9 ARRENDAMIENTOS, C12 INTERADMINISTRATIVOS, C14 INTERVENTORIAS). ' .
                                 'Muestra cantidad y valor por clase.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista los contratos de cada clase. Por defecto false.'],
                    ],
                ],
            ],

            // ── AFILIACIONES AVANZADO ──────────────────────────────────────
            [
                'name'        => 'afiliaciones_por_eps',
                'description' => 'Afiliaciones ARL agrupadas por EPS del contratista. Muestra cantidad y valor total.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'estado' => ['type' => 'string',  'description' => 'Estado: pendiente, validado, rechazado. Opcional.'],
                        'anio'   => ['type' => 'integer', 'description' => 'Año de fecha_inicio. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_con_novedad',
                'description' => 'Afiliaciones ARL que tienen novedades: prórrogas, adiciones de valor, o terminación anticipada. ' .
                                 'Útil para ver qué afiliaciones han sufrido cambios.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'tipo_novedad' => ['type' => 'string',  'description' => '"prorroga", "adicion", "terminacion_anticipada" o "todas". Por defecto "todas".'],
                        'estado'       => ['type' => 'string',  'description' => 'Estado de la afiliación. Opcional.'],
                        'anio'         => ['type' => 'integer', 'description' => 'Año. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_rechazadas',
                'description' => 'Afiliaciones con estado "rechazado". Muestra motivo de rechazo, dependencia y contratista.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'anio'               => ['type' => 'integer', 'description' => 'Año. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista una a una. Por defecto true.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_vencidas',
                'description' => 'Afiliaciones ARL cuya fecha_fin ya pasó (contrato vencido). ' .
                                 'Permite identificar contratistas cuya cobertura ARL ya expiró.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                        'estado'             => ['type' => 'string',  'description' => 'Estado de la afiliación. Opcional.'],
                        'con_detalle'        => ['type' => 'boolean', 'description' => 'Si true lista una a una. Por defecto false.'],
                    ],
                ],
            ],

            // ── BÚSQUEDAS TRANSVERSALES ────────────────────────────────────
            [
                'name'        => 'buscar_por_documento',
                'description' => 'Busca una persona por número de documento (cédula o NIT) en contratos SECOP y afiliaciones ARL. ' .
                                 'Retorna toda la información disponible sobre esa persona en el sistema.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'documento' => ['type' => 'string', 'description' => 'Número de documento (cédula o NIT). Requerido.'],
                    ],
                    'required' => ['documento'],
                ],
            ],
            [
                'name'        => 'cruce_contrato_afiliacion',
                'description' => 'Verifica cuántos contratos de la vigencia tienen su correspondiente afiliación ARL registrada. ' .
                                 'Muestra contratos CON afiliación y SIN afiliación. Útil para control y seguimiento.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'           => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
        ];
    }

    // ─── Dispatch de herramientas ─────────────────────────────────────────────

    private function ejecutar(string $nombre, array $input): array
    {
        return match ($nombre) {
            'listar_dependencias'           => $this->toolListarDependencias(),
            'resumen_contratos'             => $this->toolResumenContratos($input),
            'contratos_por_dependencia'     => $this->toolContratosPorDependencia($input),
            'contratos_detallado'           => $this->toolContratosDetallado($input),
            'contratos_proximos_vencer'     => $this->toolContratosProximosVencer($input),
            'contratos_vencidos'            => $this->toolContratosVencidos($input),
            'top_contratistas'              => $this->toolTopContratistas($input),
            'resumen_afiliaciones'          => $this->toolResumenAfiliaciones($input),
            'afiliaciones_detallado'        => $this->toolAfiliacionesDetallado($input),
            'afiliaciones_proximas_vencer'  => $this->toolAfiliacionesProximasVencer($input),
            'top_contratistas_afiliaciones' => $this->toolTopContratistasAfiliaciones($input),
            'afiliaciones_por_dependencia'  => $this->toolAfiliacionesPorDependencia($input),
            'estadisticas_afiliaciones'     => $this->toolEstadisticasAfiliaciones($input),
            'exportar_excel'                => $this->toolExportarExcel($input),
            'buscar_contratista'            => $this->toolBuscarContratista($input),
            'buscar_contrato'               => $this->toolBuscarContrato($input),
            // Nuevas herramientas
            'listar_areas'                  => $this->toolListarAreas(),
            'estadisticas_generales'        => $this->toolEstadisticasGenerales(),
            'contratos_vigentes_hoy'        => $this->toolContratosVigentesHoy($input),
            'contratos_con_prorroga'        => $this->toolContratosConProrroga($input),
            'contratos_con_adicion'         => $this->toolContratosConAdicion($input),
            'contratos_con_anticipo'        => $this->toolContratosConAnticipo($input),
            'contratos_por_supervisor'      => $this->toolContratosPorSupervisor($input),
            'presupuesto_por_fuente'        => $this->toolPresupuestoPorFuente($input),
            'contratos_por_objeto'          => $this->toolContratosPorObjeto($input),
            'contratistas_multivigencia'    => $this->toolContratistasMultivigencia($input),
            'contratos_liquidados'          => $this->toolContratosLiquidados($input),
            'contratos_por_clase'           => $this->toolContratosPorClase($input),
            'afiliaciones_por_eps'          => $this->toolAfiliacionesPorEps($input),
            'afiliaciones_con_novedad'      => $this->toolAfiliacionesConNovedad($input),
            'afiliaciones_rechazadas'       => $this->toolAfiliacionesRechazadas($input),
            'afiliaciones_vencidas'         => $this->toolAfiliacionesVencidas($input),
            'buscar_por_documento'          => $this->toolBuscarPorDocumento($input),
            'cruce_contrato_afiliacion'     => $this->toolCruceContratoAfiliacion($input),
            default                         => ['error' => "Herramienta '{$nombre}' no encontrada."],
        };
    }

    // ─── Implementaciones ─────────────────────────────────────────────────────

    private function toolListarDependencias(): array
    {
        $deps = DB::table('dependencias')->orderBy('nombre')->get(['id', 'nombre']);
        return [
            'total'        => $deps->count(),
            'dependencias' => $deps->map(fn ($d) => ['id' => $d->id, 'nombre' => $d->nombre])->toArray(),
        ];
    }

    private function toolResumenContratos(array $input): array
    {
        $q = Contrato::query();
        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }

        $total      = (clone $q)->count();
        $valorTotal = (clone $q)->sum('valor_contrato');
        $estados    = (clone $q)
            ->selectRaw('estado, count(*) as cantidad, sum(valor_contrato) as valor')
            ->groupBy('estado')->orderByDesc('cantidad')->get()
            ->map(fn ($r) => [
                'estado'   => $r->estado,
                'cantidad' => $r->cantidad,
                'valor'    => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
            ])->toArray();

        return [
            'total_contratos' => $total,
            'valor_total'     => '$' . number_format($valorTotal, 0, ',', '.'),
            'por_estado'      => $estados,
        ];
    }

    private function toolContratosPorDependencia(array $input): array
    {
        $q = Contrato::with('dependencia')
            ->selectRaw('dependencia_id, count(*) as cantidad, sum(valor_contrato) as valor')
            ->groupBy('dependencia_id');

        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);
        if (! empty($input['estado']))   $q->where('estado', $input['estado']);

        return $q->get()->map(fn ($r) => [
            'dependencia' => $r->dependencia?->nombre ?? 'Sin dependencia',
            'cantidad'    => $r->cantidad,
            'valor'       => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();
    }

    private function toolContratosDetallado(array $input): array
    {
        $q = Contrato::with('dependencia');

        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);

        // Tipo contrato (descripción)
        $tiposContrato = $input['tipos_contrato'] ?? [];
        if (! empty($input['tipo_contrato'])) $tiposContrato[] = $input['tipo_contrato'];
        if (! empty($tiposContrato)) {
            $q->where(function ($sub) use ($tiposContrato) {
                foreach ($tiposContrato as $t) {
                    $sub->orWhere('tipo_contrato', 'like', "%{$t}%");
                }
            });
        }

        // Dependencia
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }

        // Modalidad (código)
        if (! empty($input['modalidad'])) $q->where('modalidad', 'like', '%' . $input['modalidad'] . '%');

        // Rango de fechas
        if (! empty($input['trimestre'])) {
            $year = (int) ($input['vigencia'] ?? now()->year);
            [$mesInicio, $mesFin] = match ((int) $input['trimestre']) {
                1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12], default => [1, 12],
            };
            $q->whereBetween('fecha_inicio', [
                \Carbon\Carbon::create($year, $mesInicio, 1)->startOfDay(),
                \Carbon\Carbon::create($year, $mesFin, 1)->endOfMonth()->endOfDay(),
            ]);
        } elseif (! empty($input['fecha_desde']) || ! empty($input['fecha_hasta'])) {
            if (! empty($input['fecha_desde'])) $q->where('fecha_inicio', '>=', $input['fecha_desde']);
            if (! empty($input['fecha_hasta'])) $q->where('fecha_inicio', '<=', $input['fecha_hasta']);
        }

        $contratos = $q->orderBy('fecha_inicio')->get();

        $total      = $contratos->count();
        $valorTotal = $contratos->sum('valor_contrato');

        $conProrroga = $contratos->filter(fn ($c) => $c->fecha_prorroga_1 || $c->fecha_prorroga_2 || $c->fecha_prorroga_3);
        $conAdicion  = $contratos->filter(fn ($c) => $c->tieneAdiciones());

        // Agrupación por tipo de contrato:
        // - "NO APLICA" y NULL se desglosan por clase real (Arrendamientos, Interventorías, etc.)
        // - CPS profesionales y apoyo se muestran con su tipo_contrato descriptivo
        $porTipo = $contratos->groupBy(function ($c) {
            $tipo = $c->tipo_contrato ?? '';
            if ($tipo === '' || $tipo === 'NO APLICA') {
                // Usar la clase real del contrato para mayor detalle
                return $c->clase ?? 'Sin clasificar';
            }
            return $tipo;
        })->map(fn ($g, $k) => [
            'tipo_contrato' => $k,
            'cantidad'      => $g->count(),
            'valor'         => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();

        $porFuente = $contratos->groupBy(fn ($c) => $c->fuente_financiacion ?? 'No especificada')
            ->map(fn ($g, $k) => [
                'fuente_financiacion' => $k,
                'cantidad'            => $g->count(),
                'valor'               => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
            ])->sortByDesc('cantidad')->values()->toArray();

        // Dependencia: usar relación con ID si existe, si no usar el campo texto dependencia_contrato
        $porDependencia = $contratos->groupBy(function ($c) {
            return $c->dependencia?->nombre
                ?? (! empty($c->dependencia_contrato) ? $c->dependencia_contrato : 'Sin dependencia');
        })->map(fn ($g, $k) => [
            'dependencia' => $k,
            'cantidad'    => $g->count(),
            'valor'       => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();

        $resultado = [
            'total_contratos'        => $total,
            'valor_total'            => '$' . number_format($valorTotal, 0, ',', '.'),
            'con_prorroga'           => $conProrroga->count(),
            'con_adicion'            => $conAdicion->count(),
            'por_tipo_contrato'      => $porTipo,
            'por_fuente_financiacion'=> $porFuente,
            'por_dependencia'        => $porDependencia,
        ];

        if (! empty($input['con_detalle'])) {
            $resultado['contratos'] = $contratos->take(50)->map(fn ($c) => [
                'numero'        => $c->numero_contrato,
                'contratista'   => $c->getNombreContratista(),
                'clase'         => $c->clase,
                'tipo_contrato' => $c->tipo_contrato ?? 'Sin tipo',
                'modalidad'     => $c->modalidad,
                'objeto'        => str()->limit($c->objeto ?? '', 100),
                'valor'         => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'fecha_inicio'  => $c->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'     => $c->fechaEfectivaCierre()?->format('d/m/Y'),
                'fuente'        => $c->fuente_financiacion,
                'prorroga'      => $c->fecha_prorroga_1 ? 'Sí' : 'No',
                'dependencia'   => $c->dependencia?->nombre
                    ?? (! empty($c->dependencia_contrato) ? $c->dependencia_contrato : 'Sin dependencia'),
            ])->toArray();
        }

        return $resultado;
    }

    private function toolContratosProximosVencer(array $input): array
    {
        $dias  = (int) ($input['dias'] ?? 30);
        $hoy   = now()->startOfDay();
        $hasta = now()->addDays($dias)->endOfDay();

        $lista = Contrato::with('dependencia')
            ->whereIn('estado', ['EN EJECUCION', 'EN EJECUCION CON ADICION'])
            ->whereNotNull('fecha_terminacion')
            ->get()
            ->filter(fn (Contrato $c) => ($cierre = $c->fechaEfectivaCierre()) && $cierre->between($hoy, $hasta))
            ->sortBy(fn (Contrato $c) => $c->fechaEfectivaCierre())
            ->map(fn (Contrato $c) => [
                'contrato'       => $c->numero_contrato,
                'contratista'    => $c->getNombreContratista(),
                'dependencia'    => $c->dependencia?->nombre,
                'vence'          => $c->fechaEfectivaCierre()?->format('d/m/Y'),
                'dias_restantes' => (int) now()->diffInDays($c->fechaEfectivaCierre(), false),
            ])->values()->toArray();

        return ['total' => count($lista), 'contratos' => $lista];
    }

    private function toolContratosVencidos(array $input): array
    {
        $q = Contrato::with('dependencia')->where('estado', 'TERMINADO');

        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }

        $total  = (clone $q)->count();
        $porDep = (clone $q)
            ->selectRaw('dependencia_id, count(*) as cantidad')
            ->groupBy('dependencia_id')->with('dependencia')->get()
            ->map(fn ($r) => [
                'dependencia' => $r->dependencia?->nombre ?? 'Sin dependencia',
                'cantidad'    => $r->cantidad,
            ])->sortByDesc('cantidad')->values()->toArray();

        return ['total' => $total, 'por_dependencia' => $porDep];
    }

    private function toolTopContratistas(array $input): array
    {
        $limite = (int) ($input['limite'] ?? 10);
        $q = Contrato::selectRaw('
                COALESCE(nombre_persona_natural, nombre_persona_juridica) as contratista,
                count(*) as cantidad,
                sum(valor_contrato) as valor
            ')
            ->groupBy('contratista')
            ->orderByDesc('cantidad')
            ->limit($limite);

        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);

        return $q->get()->map(fn ($r) => [
            'contratista' => $r->contratista,
            'contratos'   => $r->cantidad,
            'valor'       => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
        ])->toArray();
    }

    private function toolResumenAfiliaciones(array $input): array
    {
        $q = Afiliacion::query();
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }
        if (! empty($input['anio'])) {
            $q->whereYear('fecha_inicio', $input['anio']);
        }

        $total   = (clone $q)->count();
        $estados = (clone $q)
            ->selectRaw('estado, count(*) as cantidad')
            ->groupBy('estado')->orderByDesc('cantidad')->get()
            ->map(fn ($r) => ['estado' => $r->estado, 'cantidad' => $r->cantidad])
            ->toArray();

        return ['total' => $total, 'por_estado' => $estados];
    }

    private function toolAfiliacionesDetallado(array $input): array
    {
        $q = Afiliacion::with('dependencia');

        if (! empty($input['estado']))             $q->where('estado', $input['estado']);
        if (! empty($input['nombre_arl']))         $q->where('nombre_arl', 'like', '%' . $input['nombre_arl'] . '%');
        if (! empty($input['tipo_riesgo']))        $q->where('tipo_riesgo', 'like', '%' . $input['tipo_riesgo'] . '%');
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }

        // Filtro por año / trimestre
        if (! empty($input['anio'])) {
            $anio = (int) $input['anio'];
            if (! empty($input['trimestre'])) {
                [$mesInicio, $mesFin] = match ((int) $input['trimestre']) {
                    1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12], default => [1, 12],
                };
                $q->whereBetween('fecha_inicio', [
                    \Carbon\Carbon::create($anio, $mesInicio, 1)->startOfDay(),
                    \Carbon\Carbon::create($anio, $mesFin, 1)->endOfMonth()->endOfDay(),
                ]);
            } else {
                $q->whereYear('fecha_inicio', $anio);
            }
        }

        $afiliaciones = $q->orderBy('fecha_inicio')->get();

        $total      = $afiliaciones->count();
        $valorTotal = $afiliaciones->sum('valor_contrato');

        // Agrupación
        $campoAgrupar = $input['agrupar_por'] ?? 'dependencia';
        $agrupacion = $afiliaciones->groupBy(fn ($a) => match ($campoAgrupar) {
            'estado'      => $a->estado ?? 'Sin estado',
            'arl'         => $a->nombre_arl ?? 'Sin ARL',
            'tipo_riesgo' => $a->tipo_riesgo ?? 'Sin tipo',
            default       => $a->dependencia?->nombre ?? 'Sin dependencia',
        })->map(fn ($grupo, $key) => [
            $campoAgrupar => $key,
            'cantidad'    => $grupo->count(),
            'valor'       => '$' . number_format($grupo->sum('valor_contrato'), 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();

        $resultado = [
            'total_afiliaciones' => $total,
            'valor_total'        => '$' . number_format($valorTotal, 0, ',', '.'),
            'agrupacion'         => $agrupacion,
        ];

        if (! empty($input['con_detalle'])) {
            $resultado['afiliaciones'] = $afiliaciones->take(50)->map(fn ($a) => [
                'contratista'  => $a->nombre_contratista,
                'dependencia'  => $a->dependencia?->nombre,
                'estado'       => $a->estado,
                'arl'          => $a->nombre_arl,
                'tipo_riesgo'  => $a->tipo_riesgo,
                'ibc'          => '$' . number_format($a->ibc ?? 0, 0, ',', '.'),
                'valor'        => '$' . number_format($a->valor_contrato ?? 0, 0, ',', '.'),
                'fecha_inicio' => $a->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'    => $a->fecha_fin?->format('d/m/Y'),
            ])->toArray();
        }

        return $resultado;
    }

    private function toolExportarExcel(array $input): array
    {
        $tipo = $input['tipo'] ?? 'contratos';

        if ($tipo === 'afiliaciones') {
            return $this->exportarAfiliacionesExcel($input);
        }

        return $this->exportarContratosExcel($input);
    }

    private function exportarContratosExcel(array $input): array
    {
        $q = Contrato::with('dependencia');

        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);

        $tiposContrato = $input['tipos_contrato'] ?? [];
        if (! empty($input['tipo_contrato'])) $tiposContrato[] = $input['tipo_contrato'];
        if (! empty($tiposContrato)) {
            $q->where(function ($sub) use ($tiposContrato) {
                foreach ($tiposContrato as $t) {
                    $sub->orWhere('tipo_contrato', 'like', "%{$t}%");
                }
            });
        }

        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }

        if (! empty($input['trimestre'])) {
            $year = (int) ($input['vigencia'] ?? now()->year);
            [$mesInicio, $mesFin] = match ((int) $input['trimestre']) {
                1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12], default => [1, 12],
            };
            $q->whereBetween('fecha_inicio', [
                \Carbon\Carbon::create($year, $mesInicio, 1)->startOfDay(),
                \Carbon\Carbon::create($year, $mesFin, 1)->endOfMonth()->endOfDay(),
            ]);
        } elseif (! empty($input['fecha_desde']) || ! empty($input['fecha_hasta'])) {
            if (! empty($input['fecha_desde'])) $q->where('fecha_inicio', '>=', $input['fecha_desde']);
            if (! empty($input['fecha_hasta'])) $q->where('fecha_inicio', '<=', $input['fecha_hasta']);
        }

        $contratos = $q->orderBy('fecha_inicio')->get();

        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contratos');

        // Encabezados
        $headers = ['N°','No. Contrato','Contratista','Tipo Contrato','Clase','Objeto','Valor','Fecha Inicio','Fecha Fin','Fuente Financiación','Prórroga','Dependencia'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $h);
        }

        // Estilo encabezado
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A1:{$lastCol}1";
        $sheet->getStyle($headerRange)->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Datos
        foreach ($contratos as $i => $c) {
            $row = $i + 2;
            $dep = $c->dependencia?->nombre
                ?? (! empty($c->dependencia_contrato) ? $c->dependencia_contrato : '');

            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $c->numero_contrato);
            $sheet->setCellValue("C{$row}", $c->getNombreContratista());
            $sheet->setCellValue("D{$row}", $c->tipo_contrato ?? 'Sin tipo');
            $sheet->setCellValue("E{$row}", $c->clase ?? '');
            $sheet->setCellValue("F{$row}", $c->objeto ?? '');
            $sheet->getCell("G{$row}")->setValueExplicit(
                $c->valor_contrato ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
            );
            $sheet->getStyle("G{$row}")->getNumberFormat()->setFormatCode('$ #,##0');
            $sheet->setCellValue("H{$row}", $c->fecha_inicio?->format('d/m/Y') ?? '');
            $sheet->setCellValue("I{$row}", $c->fechaEfectivaCierre()?->format('d/m/Y') ?? '');
            $sheet->setCellValue("J{$row}", $c->fuente_financiacion ?? '');
            $sheet->setCellValue("K{$row}", $c->fecha_prorroga_1 ? 'Sí' : 'No');
            $sheet->setCellValue("L{$row}", $dep);

            // Fila par/impar
            $bgColor = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'DBEAFE']]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
        }

        // Ancho de columnas
        foreach (['A' => 6, 'B' => 14, 'C' => 30, 'D' => 32, 'E' => 22, 'F' => 55,
                  'G' => 18, 'H' => 13, 'I' => 13, 'J' => 18, 'K' => 10, 'L' => 30] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Fila objeto con wrap
        $sheet->getStyle('F2:F' . ($contratos->count() + 1))->getAlignment()->setWrapText(true);

        // Fila de totales
        $totalRow = $contratos->count() + 2;
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->setCellValue("B{$totalRow}", $contratos->count() . ' contratos');
        $sheet->getCell("G{$totalRow}")->setValueExplicit(
            $contratos->sum('valor_contrato'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC
        );
        $sheet->getStyle("G{$totalRow}")->getNumberFormat()->setFormatCode('$ #,##0');
        $sheet->getStyle("A{$totalRow}:{$lastCol}{$totalRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
        ]);

        // Guardar archivo
        $nombreBase = $input['nombre_archivo'] ?? ('contratos_' . now()->format('Ymd_His'));
        $nombreBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombreBase);
        $filename = "{$nombreBase}.xlsx";
        $path = storage_path("app/public/exports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return [
            'url'      => asset("storage/exports/{$filename}"),
            'filename' => $filename,
            'total'    => $contratos->count(),
            'valor_total' => '$' . number_format($contratos->sum('valor_contrato'), 0, ',', '.'),
        ];
    }

    private function exportarAfiliacionesExcel(array $input): array
    {
        $q = Afiliacion::with('dependencia');

        if (! empty($input['estado']))             $q->where('estado', $input['estado']);
        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }
        if (! empty($input['trimestre']) && ! empty($input['vigencia'])) {
            $year = (int) $input['vigencia'];
            [$mesInicio, $mesFin] = match ((int) $input['trimestre']) {
                1 => [1, 3], 2 => [4, 6], 3 => [7, 9], 4 => [10, 12], default => [1, 12],
            };
            $q->whereBetween('fecha_inicio', [
                \Carbon\Carbon::create($year, $mesInicio, 1)->startOfDay(),
                \Carbon\Carbon::create($year, $mesFin, 1)->endOfMonth()->endOfDay(),
            ]);
        }

        $afiliaciones = $q->orderBy('created_at')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Afiliaciones ARL');

        $headers = ['N°','Contratista','Documento','No. Contrato','ARL','Tipo Riesgo','EPS','IBC','Valor Contrato','Estado','Fecha Inicio','Fecha Fin','Dependencia'];
        foreach ($headers as $i => $h) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}1", $h);
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D4ED8']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        foreach ($afiliaciones as $i => $a) {
            $row = $i + 2;
            $sheet->setCellValue("A{$row}", $i + 1);
            $sheet->setCellValue("B{$row}", $a->nombre_contratista);
            $sheet->setCellValue("C{$row}", ($a->tipo_documento ?? '') . ' ' . ($a->numero_documento ?? ''));
            $sheet->setCellValue("D{$row}", $a->numero_contrato);
            $sheet->setCellValue("E{$row}", $a->nombre_arl);
            $sheet->setCellValue("F{$row}", $a->tipo_riesgo);
            $sheet->setCellValue("G{$row}", $a->eps);
            $sheet->getCell("H{$row}")->setValueExplicit($a->ibc ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle("H{$row}")->getNumberFormat()->setFormatCode('$ #,##0');
            $sheet->getCell("I{$row}")->setValueExplicit($a->valor_contrato ?? 0, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('$ #,##0');
            $sheet->setCellValue("J{$row}", $a->estado);
            $sheet->setCellValue("K{$row}", $a->fecha_inicio?->format('d/m/Y') ?? '');
            $sheet->setCellValue("L{$row}", $a->fecha_fin?->format('d/m/Y') ?? '');
            $sheet->setCellValue("M{$row}", $a->dependencia?->nombre ?? '');

            $bgColor = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($bgColor);
        }

        foreach (['A' => 5, 'B' => 30, 'C' => 18, 'D' => 16, 'E' => 16, 'F' => 12,
                  'G' => 16, 'H' => 16, 'I' => 18, 'J' => 14, 'K' => 13, 'L' => 13, 'M' => 30] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        $nombreBase = $input['nombre_archivo'] ?? ('afiliaciones_' . now()->format('Ymd_His'));
        $nombreBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $nombreBase);
        $filename = "{$nombreBase}.xlsx";
        $path = storage_path("app/public/exports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        (new Xlsx($spreadsheet))->save($path);

        return [
            'url'      => asset("storage/exports/{$filename}"),
            'filename' => $filename,
            'total'    => $afiliaciones->count(),
        ];
    }

    private function toolAfiliacionesProximasVencer(array $input): array
    {
        $dias = (int) ($input['dias'] ?? 30);

        $lista = Afiliacion::with('dependencia')
            ->where('estado', 'validado')
            ->where(function ($q) use ($dias) {
                $q->where(function ($q2) use ($dias) {
                    $q2->where('tiene_prorroga', false)
                       ->where('fecha_fin', '>=', now())
                       ->where('fecha_fin', '<=', now()->addDays($dias));
                })->orWhere(function ($q2) use ($dias) {
                    $q2->where('tiene_prorroga', true)
                       ->where('nueva_fecha_fin_prorroga', '>=', now())
                       ->where('nueva_fecha_fin_prorroga', '<=', now()->addDays($dias));
                });
            })
            ->orderBy('fecha_fin')
            ->get()
            ->map(fn ($a) => [
                'contratista'    => $a->nombre_contratista,
                'dependencia'    => $a->dependencia?->nombre,
                'vence'          => ($a->tiene_prorroga ? $a->nueva_fecha_fin_prorroga : $a->fecha_fin)?->format('d/m/Y'),
                'dias_restantes' => (int) now()->diffInDays(
                    $a->tiene_prorroga ? $a->nueva_fecha_fin_prorroga : $a->fecha_fin, false
                ),
            ])->toArray();

        return ['total' => count($lista), 'afiliaciones' => $lista];
    }

    private function toolTopContratistasAfiliaciones(array $input): array
    {
        $limite = (int) ($input['limite'] ?? 10);
        $q = Afiliacion::selectRaw('nombre_contratista, count(*) as cantidad, sum(valor_contrato) as valor_total')
            ->groupBy('nombre_contratista')
            ->orderByDesc('cantidad')
            ->limit($limite);

        if (! empty($input['estado'])) $q->where('estado', $input['estado']);

        return $q->get()->map(fn ($r) => [
            'contratista'  => $r->nombre_contratista,
            'afiliaciones' => $r->cantidad,
            'valor_total'  => '$' . number_format($r->valor_total ?? 0, 0, ',', '.'),
        ])->toArray();
    }

    private function toolAfiliacionesPorDependencia(array $input): array
    {
        $q = Afiliacion::with('dependencia')
            ->selectRaw('dependencia_id, count(*) as cantidad, sum(valor_contrato) as valor')
            ->groupBy('dependencia_id');

        if (! empty($input['estado'])) $q->where('estado', $input['estado']);
        if (! empty($input['anio']))   $q->whereYear('fecha_inicio', $input['anio']);

        return $q->get()->map(fn ($r) => [
            'dependencia' => $r->dependencia?->nombre ?? 'Sin dependencia',
            'cantidad'    => $r->cantidad,
            'valor'       => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();
    }

    private function toolEstadisticasAfiliaciones(array $input): array
    {
        $campo = match ($input['agrupar_por'] ?? 'arl') {
            'tipo_riesgo' => 'tipo_riesgo',
            'eps'         => 'eps',
            'estado'      => 'estado',
            default       => 'nombre_arl',
        };
        $label = $campo === 'nombre_arl' ? 'arl' : $campo;

        $q = Afiliacion::selectRaw("{$campo} as agrupacion, count(*) as cantidad, avg(ibc) as ibc_promedio, sum(valor_contrato) as valor_total")
            ->groupBy($campo)
            ->orderByDesc('cantidad');

        if (! empty($input['anio'])) $q->whereYear('fecha_inicio', $input['anio']);

        return $q->get()->map(fn ($r) => [
            $label        => $r->agrupacion ?? 'No especificado',
            'cantidad'    => $r->cantidad,
            'ibc_prom'    => '$' . number_format($r->ibc_promedio ?? 0, 0, ',', '.'),
            'valor_total' => '$' . number_format($r->valor_total ?? 0, 0, ',', '.'),
        ])->toArray();
    }

    private function toolBuscarContratista(array $input): array
    {
        $nombre = trim($input['nombre'] ?? '');
        $limite = (int) ($input['limite'] ?? 10);

        // Dividir en palabras para buscar "mike piedrahita" → registros con "mike" Y "piedrahita"
        $palabras = array_filter(explode(' ', $nombre));

        $contratos = Contrato::with('dependencia')
            ->where(function ($q) use ($palabras, $nombre) {
                // Primero intentar frase completa
                $q->where('nombre_persona_natural', 'like', "%{$nombre}%")
                  ->orWhere('nombre_persona_juridica', 'like', "%{$nombre}%");
                // Luego buscar por cada palabra (AND)
                if (count($palabras) > 1) {
                    $q->orWhere(function ($q2) use ($palabras) {
                        foreach ($palabras as $p) {
                            $q2->where(function ($q3) use ($p) {
                                $q3->where('nombre_persona_natural', 'like', "%{$p}%")
                                   ->orWhere('nombre_persona_juridica', 'like', "%{$p}%");
                            });
                        }
                    });
                }
            })
            ->latest()->limit($limite)->get()
            ->map(fn ($c) => [
                'numero'        => $c->numero_contrato,
                'tipo_contrato' => $c->tipo_contrato,
                'estado'        => $c->estado,
                'vigencia'      => $c->vigencia,
                'valor'         => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'dependencia'   => $c->dependencia?->nombre ?? $c->dependencia_contrato,
                'objeto'        => str()->limit($c->objeto ?? '', 120),
            ])->toArray();

        $afiliaciones = Afiliacion::with('dependencia')
            ->where(function ($q) use ($palabras, $nombre) {
                $q->where('nombre_contratista', 'like', "%{$nombre}%");
                if (count($palabras) > 1) {
                    $q->orWhere(function ($q2) use ($palabras) {
                        foreach ($palabras as $p) {
                            $q2->where('nombre_contratista', 'like', "%{$p}%");
                        }
                    });
                }
            })
            ->latest()->limit($limite)->get()
            ->map(fn ($a) => [
                'estado'       => $a->estado,
                'arl'          => $a->nombre_arl,
                'tipo_riesgo'  => $a->tipo_riesgo,
                'dependencia'  => $a->dependencia?->nombre,
                'fecha_inicio' => $a->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'    => $a->fecha_fin?->format('d/m/Y'),
                'valor'        => '$' . number_format($a->valor_contrato ?? 0, 0, ',', '.'),
            ])->toArray();

        return [
            'busqueda'           => $nombre,
            'total_contratos'    => count($contratos),
            'total_afiliaciones' => count($afiliaciones),
            'contratos'          => $contratos,
            'afiliaciones'       => $afiliaciones,
        ];
    }

    private function toolBuscarContrato(array $input): array
    {
        $numero   = (int) ($input['numero'] ?? 0);
        $vigencia = isset($input['vigencia']) ? (int) $input['vigencia'] : null;

        $q = Contrato::with('dependencia')->where('numero_contrato', $numero);
        if ($vigencia) $q->where('vigencia', $vigencia);

        $contratos = $q->get()->map(fn ($c) => [
            'numero'              => $c->numero_contrato,
            'vigencia'            => $c->vigencia,
            'tipo_contrato'       => $c->tipo_contrato,
            'clase'               => $c->clase,
            'estado'              => $c->estado,
            'modalidad'           => $c->modalidad,
            'fuente_financiacion' => $c->fuente_financiacion,
            'valor'               => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
            'fecha_inicio'        => $c->fecha_inicio?->format('d/m/Y'),
            'fecha_fin'           => $c->fecha_fin?->format('d/m/Y'),
            'dependencia'         => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            'contratista'         => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
            'objeto'              => $c->objeto,
            'numero_secop'        => $c->numero_secop,
        ])->toArray();

        if (empty($contratos)) {
            return ['encontrado' => false, 'mensaje' => "No se encontró el contrato número {$numero}" . ($vigencia ? " de la vigencia {$vigencia}" : '') . '.'];
        }

        return [
            'encontrado' => true,
            'total'      => count($contratos),
            'contratos'  => $contratos,
        ];
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // HERRAMIENTAS NUEVAS
    // ═══════════════════════════════════════════════════════════════════════════

    private function toolListarAreas(): array
    {
        $areas = \App\Models\Area::with('dependencia')->orderBy('nombre')->get();
        return [
            'total' => $areas->count(),
            'areas' => $areas->map(fn ($a) => [
                'id'          => $a->id,
                'nombre'      => $a->nombre,
                'codigo'      => $a->codigo,
                'dependencia' => $a->dependencia?->nombre,
            ])->toArray(),
        ];
    }

    private function toolEstadisticasGenerales(): array
    {
        $totalContratos    = Contrato::count();
        $valorContratos    = Contrato::sum('valor_contrato');
        $vigencias         = Contrato::distinct()->orderByDesc('vigencia')->pluck('vigencia')->toArray();
        $contratistasSecop = Contrato::whereNotNull('nombre_persona_natural')
            ->distinct()->count('nombre_persona_natural');
        $contratistasJur   = Contrato::whereNotNull('nombre_persona_juridica')
            ->distinct()->count('nombre_persona_juridica');
        $enEjecucion       = Contrato::where('estado', 'like', '%EN EJECUCION%')->count();
        $terminados        = Contrato::where('estado', 'TERMINADO')->count();

        $totalAfiliaciones = Afiliacion::count();
        $valorAfiliaciones = Afiliacion::sum('valor_contrato');
        $pendientes        = Afiliacion::where('estado', 'pendiente')->count();
        $validadas         = Afiliacion::where('estado', 'validado')->count();
        $rechazadas        = Afiliacion::where('estado', 'rechazado')->count();
        $contratistasArl   = Afiliacion::distinct()->count('numero_documento');

        return [
            'contratos_secop' => [
                'total'            => $totalContratos,
                'valor_total'      => '$' . number_format($valorContratos, 0, ',', '.'),
                'en_ejecucion'     => $enEjecucion,
                'terminados'       => $terminados,
                'contratistas_nat' => $contratistasSecop,
                'contratistas_jur' => $contratistasJur,
                'vigencias'        => $vigencias,
            ],
            'afiliaciones_arl' => [
                'total'          => $totalAfiliaciones,
                'valor_total'    => '$' . number_format($valorAfiliaciones, 0, ',', '.'),
                'pendientes'     => $pendientes,
                'validadas'      => $validadas,
                'rechazadas'     => $rechazadas,
                'contratistas'   => $contratistasArl,
            ],
        ];
    }

    private function toolContratosVigentesHoy(array $input): array
    {
        $hoy = now()->toDateString();
        $q = Contrato::with('dependencia')
            ->where('fecha_inicio', '<=', $hoy)
            ->where(function ($q) use ($hoy) {
                $q->whereRaw("COALESCE(
                    CASE WHEN tiene_prorroga_3 IS NOT NULL THEN fecha_prorroga_3
                         WHEN tiene_prorroga_2 IS NOT NULL THEN fecha_prorroga_2
                         WHEN tiene_prorroga_1 IS NOT NULL THEN fecha_prorroga_1
                         ELSE fecha_terminacion END, fecha_terminacion) >= ?", [$hoy])
                  ->orWhere('fecha_terminacion', '>=', $hoy);
            })
            ->where('estado', 'like', '%EN EJECUCION%');

        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();
        $resumen = [
            'fecha_consulta'     => now()->format('d/m/Y'),
            'total'              => $contratos->count(),
            'valor_total'        => '$' . number_format($contratos->sum('valor_contrato'), 0, ',', '.'),
            'por_dependencia'    => $contratos->groupBy(fn ($c) =>
                $c->dependencia?->nombre ?? ($c->dependencia_contrato ?? 'Sin dependencia')
            )->map(fn ($g) => ['cantidad' => $g->count(), 'valor' => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.')])->toArray(),
        ];

        if (! empty($input['con_detalle'])) {
            $resumen['contratos'] = $contratos->take(50)->map(fn ($c) => [
                'numero'      => $c->numero_contrato,
                'vigencia'    => $c->vigencia,
                'contratista' => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'tipo'        => $c->tipo_contrato ?? $c->clase,
                'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'fecha_fin'   => $c->fecha_terminacion?->format('d/m/Y'),
                'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray();
        }

        return $resumen;
    }

    private function toolContratosConProrroga(array $input): array
    {
        $q = Contrato::with('dependencia')
            ->whereNotNull('fecha_prorroga_1');

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();
        $resumen = [
            'total'          => $contratos->count(),
            'valor_original' => '$' . number_format($contratos->sum('valor_contrato'), 0, ',', '.'),
            'con_1_prorroga' => $contratos->whereNull('fecha_prorroga_2')->count(),
            'con_2_prorrogas'=> $contratos->whereNotNull('fecha_prorroga_2')->whereNull('fecha_prorroga_3')->count(),
            'con_3_prorrogas'=> $contratos->whereNotNull('fecha_prorroga_3')->count(),
        ];

        if (! empty($input['con_detalle'])) {
            $resumen['contratos'] = $contratos->take(50)->map(fn ($c) => [
                'numero'         => $c->numero_contrato,
                'vigencia'       => $c->vigencia,
                'contratista'    => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'fecha_original' => $c->fecha_terminacion?->format('d/m/Y'),
                'prorroga_1'     => $c->fecha_prorroga_1?->format('d/m/Y'),
                'prorroga_2'     => $c->fecha_prorroga_2?->format('d/m/Y'),
                'prorroga_3'     => $c->fecha_prorroga_3?->format('d/m/Y'),
                'dias_adicionales'=> ($c->plazo_dias_prorroga_1 ?? 0) + ($c->plazo_dias_prorroga_2 ?? 0) + ($c->plazo_dias_prorroga_3 ?? 0),
                'dependencia'    => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray();
        }

        return $resumen;
    }

    private function toolContratosConAdicion(array $input): array
    {
        $q = Contrato::with('dependencia')
            ->whereNotNull('valor_adicional_1')
            ->where('valor_adicional_1', '>', 0);

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();
        $valorOriginal = $contratos->sum('valor_contrato');
        $valorAdicion  = $contratos->sum(fn ($c) =>
            ($c->valor_adicional_1 ?? 0) + ($c->valor_adicional_2 ?? 0) + ($c->valor_adicional_3 ?? 0)
        );

        $resumen = [
            'total'          => $contratos->count(),
            'valor_original' => '$' . number_format($valorOriginal, 0, ',', '.'),
            'valor_adicionado'=> '$' . number_format($valorAdicion, 0, ',', '.'),
            'valor_total'    => '$' . number_format($valorOriginal + $valorAdicion, 0, ',', '.'),
        ];

        if (! empty($input['con_detalle'])) {
            $resumen['contratos'] = $contratos->take(50)->map(fn ($c) => [
                'numero'          => $c->numero_contrato,
                'vigencia'        => $c->vigencia,
                'contratista'     => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'valor_original'  => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'adicion_1'       => $c->valor_adicional_1 ? '$' . number_format($c->valor_adicional_1, 0, ',', '.') : null,
                'adicion_2'       => $c->valor_adicional_2 ? '$' . number_format($c->valor_adicional_2, 0, ',', '.') : null,
                'adicion_3'       => $c->valor_adicional_3 ? '$' . number_format($c->valor_adicional_3, 0, ',', '.') : null,
                'dependencia'     => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray();
        }

        return $resumen;
    }

    private function toolContratosConAnticipo(array $input): array
    {
        $q = Contrato::with('dependencia')
            ->where('tiene_anticipo', true);

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();
        return [
            'total'         => $contratos->count(),
            'valor_anticipo'=> '$' . number_format($contratos->sum('valor_anticipo'), 0, ',', '.'),
            'contratos'     => $contratos->map(fn ($c) => [
                'numero'      => $c->numero_contrato,
                'vigencia'    => $c->vigencia,
                'contratista' => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'tipo'        => $c->tipo_anticipo,
                'porcentaje'  => $c->porcentaje_anticipo . '%',
                'valor'       => '$' . number_format($c->valor_anticipo ?? 0, 0, ',', '.'),
                'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray(),
        ];
    }

    private function toolContratosPorSupervisor(array $input): array
    {
        $q = Contrato::with('dependencia')
            ->whereNotNull('nombre_supervisor');

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }
        if (! empty($input['nombre_supervisor'])) {
            $q->where('nombre_supervisor', 'like', '%' . $input['nombre_supervisor'] . '%');
        }

        $contratos = $q->get();

        $porSupervisor = $contratos->groupBy('nombre_supervisor')->map(function ($g, $sup) use ($input) {
            $entry = [
                'supervisor'  => $sup,
                'cargo'       => $g->first()->cargo_supervisor,
                'cantidad'    => $g->count(),
                'valor_total' => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
            ];
            if (! empty($input['con_detalle'])) {
                $entry['contratos'] = $g->map(fn ($c) => [
                    'numero'      => $c->numero_contrato,
                    'vigencia'    => $c->vigencia,
                    'contratista' => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                    'tipo'        => $c->tipo_contrato ?? $c->clase,
                    'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                    'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
                ])->toArray();
            }
            return $entry;
        })->sortByDesc('cantidad')->values()->toArray();

        return ['total_contratos' => $contratos->count(), 'por_supervisor' => $porSupervisor];
    }

    private function toolPresupuestoPorFuente(array $input): array
    {
        $q = Contrato::whereNotNull('fuente_financiacion');

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $porFuente = $q->selectRaw('fuente_financiacion, count(*) as cantidad, sum(valor_contrato) as valor, sum(recursos_sgp) as sgp, sum(recursos_sgr) as sgr, sum(recursos_pgn) as pgn, sum(otros_recursos) as otros')
            ->groupBy('fuente_financiacion')
            ->orderByDesc('valor')
            ->get()
            ->map(fn ($r) => [
                'fuente'    => $r->fuente_financiacion,
                'cantidad'  => $r->cantidad,
                'valor'     => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
                'sgp'       => $r->sgp  ? '$' . number_format($r->sgp,  0, ',', '.') : null,
                'sgr'       => $r->sgr  ? '$' . number_format($r->sgr,  0, ',', '.') : null,
                'pgn'       => $r->pgn  ? '$' . number_format($r->pgn,  0, ',', '.') : null,
                'otros'     => $r->otros ? '$' . number_format($r->otros, 0, ',', '.') : null,
            ])->toArray();

        $total = Contrato::when(! empty($input['vigencia']), fn ($q) => $q->where('vigencia', $input['vigencia']))->sum('valor_contrato');

        return [
            'valor_total'  => '$' . number_format($total, 0, ',', '.'),
            'por_fuente'   => $porFuente,
        ];
    }

    private function toolContratosPorObjeto(array $input): array
    {
        $texto  = trim($input['texto'] ?? '');
        $limite = (int) ($input['limite'] ?? 15);

        if (empty($texto)) return ['error' => 'Parámetro texto requerido.'];

        $palabras = array_filter(explode(' ', $texto));
        $q = Contrato::with('dependencia');

        // Buscar cada palabra en el objeto (AND)
        foreach ($palabras as $p) {
            $q->where('objeto', 'like', "%{$p}%");
        }

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->limit($limite)->get();
        return [
            'busqueda' => $texto,
            'total'    => $contratos->count(),
            'contratos'=> $contratos->map(fn ($c) => [
                'numero'      => $c->numero_contrato,
                'vigencia'    => $c->vigencia,
                'contratista' => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'tipo'        => $c->tipo_contrato ?? $c->clase,
                'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'estado'      => $c->estado,
                'objeto'      => str()->limit($c->objeto ?? '', 150),
                'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray(),
        ];
    }

    private function toolContratistasMultivigencia(array $input): array
    {
        $minVigencias = (int) ($input['minimo_vigencias'] ?? 2);
        $limite       = (int) ($input['limite'] ?? 20);

        $data = Contrato::whereNotNull('nombre_persona_natural')
            ->selectRaw('nombre_persona_natural as contratista, cedula as documento, count(distinct vigencia) as num_vigencias, count(*) as total_contratos, sum(valor_contrato) as valor_total, min(vigencia) as primera_vigencia, max(vigencia) as ultima_vigencia')
            ->groupBy('nombre_persona_natural', 'cedula')
            ->havingRaw('count(distinct vigencia) >= ?', [$minVigencias])
            ->orderByDesc('num_vigencias')
            ->orderByDesc('total_contratos')
            ->limit($limite)
            ->get()
            ->map(fn ($r) => [
                'contratista'      => $r->contratista,
                'documento'        => $r->documento,
                'vigencias'        => $r->num_vigencias,
                'total_contratos'  => $r->total_contratos,
                'valor_total'      => '$' . number_format($r->valor_total ?? 0, 0, ',', '.'),
                'primera_vigencia' => $r->primera_vigencia,
                'ultima_vigencia'  => $r->ultima_vigencia,
            ])->toArray();

        return ['total_contratistas' => count($data), 'contratistas' => $data];
    }

    private function toolContratosLiquidados(array $input): array
    {
        $q = Contrato::with('dependencia')->whereNotNull('fecha_acta_liquidacion');

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();
        return [
            'total'           => $contratos->count(),
            'valor_contratos' => '$' . number_format($contratos->sum('valor_contrato'), 0, ',', '.'),
            'valor_liquidado' => '$' . number_format($contratos->sum('valor_acta_liquidacion'), 0, ',', '.'),
            'valor_revertido' => '$' . number_format($contratos->sum('valor_reversion'), 0, ',', '.'),
            'contratos'       => $contratos->map(fn ($c) => [
                'numero'           => $c->numero_contrato,
                'vigencia'         => $c->vigencia,
                'contratista'      => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                'valor_contrato'   => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'valor_liquidado'  => $c->valor_acta_liquidacion ? '$' . number_format($c->valor_acta_liquidacion, 0, ',', '.') : null,
                'fecha_liquidacion'=> $c->fecha_acta_liquidacion?->format('d/m/Y'),
                'valor_reversion'  => $c->valor_reversion ? '$' . number_format($c->valor_reversion, 0, ',', '.') : null,
                'dependencia'      => $c->dependencia?->nombre ?? $c->dependencia_contrato,
            ])->toArray(),
        ];
    }

    private function toolContratosPorClase(array $input): array
    {
        $q = Contrato::with('dependencia');

        if (! empty($input['vigencia']))           $q->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $q->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $q->get();

        $porClase = $contratos->groupBy(fn ($c) => $c->clase ?? 'Sin clasificar')
            ->map(function ($g, $clase) use ($input) {
                $entry = [
                    'clase'      => $clase,
                    'cantidad'   => $g->count(),
                    'valor'      => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
                ];
                if (! empty($input['con_detalle'])) {
                    $entry['contratos'] = $g->take(20)->map(fn ($c) => [
                        'numero'      => $c->numero_contrato,
                        'vigencia'    => $c->vigencia,
                        'contratista' => $c->nombre_persona_natural ?? $c->nombre_persona_juridica,
                        'tipo'        => $c->tipo_contrato,
                        'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                        'estado'      => $c->estado,
                        'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
                    ])->toArray();
                }
                return $entry;
            })->sortByDesc('cantidad')->values()->toArray();

        return ['total' => $contratos->count(), 'por_clase' => $porClase];
    }

    private function toolAfiliacionesPorEps(array $input): array
    {
        $q = Afiliacion::selectRaw('eps, count(*) as cantidad, sum(valor_contrato) as valor')
            ->groupBy('eps')
            ->orderByDesc('cantidad');

        if (! empty($input['estado'])) $q->where('estado', $input['estado']);
        if (! empty($input['anio']))   $q->whereYear('fecha_inicio', $input['anio']);

        return [
            'por_eps' => $q->get()->map(fn ($r) => [
                'eps'      => $r->eps ?? 'No registrada',
                'cantidad' => $r->cantidad,
                'valor'    => '$' . number_format($r->valor ?? 0, 0, ',', '.'),
            ])->toArray(),
        ];
    }

    private function toolAfiliacionesConNovedad(array $input): array
    {
        $tipo  = $input['tipo_novedad'] ?? 'todas';
        $estado = $input['estado'] ?? null;
        $anio  = $input['anio'] ?? null;

        $q = Afiliacion::with('dependencia');
        if ($estado) $q->where('estado', $estado);
        if ($anio)   $q->whereYear('fecha_inicio', $anio);

        if ($tipo === 'prorroga' || $tipo === 'todas') {
            $qP = clone $q;
            $qP->where('tiene_prorroga', true);
            $prorrogas = $qP->count();
        }
        if ($tipo === 'adicion' || $tipo === 'todas') {
            $qA = clone $q;
            $qA->where('tiene_adicion', true);
            $adiciones = $qA->count();
        }
        if ($tipo === 'terminacion_anticipada' || $tipo === 'todas') {
            $qT = clone $q;
            $qT->where('tiene_terminacion_anticipada', true);
            $anticipadas = $qT->count();
        }

        // Listado consolidado
        $qLista = Afiliacion::with('dependencia');
        if ($estado) $qLista->where('estado', $estado);
        if ($anio)   $qLista->whereYear('fecha_inicio', $anio);
        if ($tipo === 'prorroga')              $qLista->where('tiene_prorroga', true);
        elseif ($tipo === 'adicion')           $qLista->where('tiene_adicion', true);
        elseif ($tipo === 'terminacion_anticipada') $qLista->where('tiene_terminacion_anticipada', true);
        else $qLista->where(function ($q2) {
            $q2->where('tiene_prorroga', true)
               ->orWhere('tiene_adicion', true)
               ->orWhere('tiene_terminacion_anticipada', true);
        });

        $lista = $qLista->get();

        return [
            'total_con_novedad'   => $lista->count(),
            'con_prorroga'        => $prorrogas ?? null,
            'con_adicion'         => $adiciones ?? null,
            'con_terminacion_ant' => $anticipadas ?? null,
            'afiliaciones'        => $lista->take(30)->map(fn ($a) => [
                'nombre'            => $a->nombre_contratista,
                'numero_contrato'   => $a->numero_contrato,
                'estado'            => $a->estado,
                'tiene_prorroga'    => $a->tiene_prorroga,
                'tiene_adicion'     => $a->tiene_adicion,
                'terminacion_ant'   => $a->tiene_terminacion_anticipada,
                'dependencia'       => $a->dependencia?->nombre,
            ])->toArray(),
        ];
    }

    private function toolAfiliacionesRechazadas(array $input): array
    {
        $q = Afiliacion::with('dependencia')->where('estado', 'rechazado');

        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }
        if (! empty($input['anio'])) $q->whereYear('fecha_inicio', $input['anio']);

        $lista = $q->latest()->get();

        return [
            'total'         => $lista->count(),
            'afiliaciones'  => $lista->map(fn ($a) => [
                'nombre'            => $a->nombre_contratista,
                'documento'         => $a->numero_documento,
                'dependencia'       => $a->dependencia?->nombre,
                'numero_contrato'   => $a->numero_contrato,
                'motivo_rechazo'    => $a->motivo_rechazo,
                'observaciones'     => $a->observaciones,
                'fecha_rechazo'     => $a->fecha_validacion?->format('d/m/Y'),
            ])->toArray(),
        ];
    }

    private function toolAfiliacionesVencidas(array $input): array
    {
        $hoy = now()->toDateString();
        $q = Afiliacion::with('dependencia')->where('fecha_fin', '<', $hoy);

        if (! empty($input['dependencia_nombre'])) {
            $q->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', '%' . $input['dependencia_nombre'] . '%'));
        }
        if (! empty($input['estado'])) $q->where('estado', $input['estado']);

        $lista = $q->get();

        $resumen = [
            'fecha_consulta' => now()->format('d/m/Y'),
            'total_vencidas'  => $lista->count(),
            'por_estado'      => $lista->groupBy('estado')->map->count()->toArray(),
            'por_dependencia' => $lista->groupBy(fn ($a) => $a->dependencia?->nombre ?? 'Sin dependencia')
                ->map->count()->sortDesc()->toArray(),
        ];

        if (! empty($input['con_detalle'])) {
            $resumen['afiliaciones'] = $lista->take(50)->map(fn ($a) => [
                'nombre'          => $a->nombre_contratista,
                'numero_contrato' => $a->numero_contrato,
                'estado'          => $a->estado,
                'fecha_fin'       => $a->fecha_fin?->format('d/m/Y'),
                'arl'             => $a->nombre_arl,
                'dependencia'     => $a->dependencia?->nombre,
            ])->toArray();
        }

        return $resumen;
    }

    private function toolBuscarPorDocumento(array $input): array
    {
        $doc = trim($input['documento'] ?? '');
        if (empty($doc)) return ['error' => 'Parámetro documento requerido.'];

        $contratos = Contrato::with('dependencia')
            ->where(function ($q) use ($doc) {
                $q->where('cedula', $doc)
                  ->orWhere('nit_contratista', $doc);
            })->get()->map(fn ($c) => [
                'numero'      => $c->numero_contrato,
                'vigencia'    => $c->vigencia,
                'tipo'        => $c->tipo_contrato ?? $c->clase,
                'estado'      => $c->estado,
                'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'fecha_inicio'=> $c->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'   => $c->fecha_terminacion?->format('d/m/Y'),
                'dependencia' => $c->dependencia?->nombre ?? $c->dependencia_contrato,
                'objeto'      => str()->limit($c->objeto ?? '', 100),
            ])->toArray();

        $afiliaciones = Afiliacion::with('dependencia')
            ->where('numero_documento', $doc)
            ->get()->map(fn ($a) => [
                'nombre'          => $a->nombre_contratista,
                'estado'          => $a->estado,
                'arl'             => $a->nombre_arl,
                'tipo_riesgo'     => $a->tipo_riesgo,
                'eps'             => $a->eps,
                'afp'             => $a->afp,
                'numero_contrato' => $a->numero_contrato,
                'fecha_inicio'    => $a->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'       => $a->fecha_fin?->format('d/m/Y'),
                'dependencia'     => $a->dependencia?->nombre,
            ])->toArray();

        if (empty($contratos) && empty($afiliaciones)) {
            return ['encontrado' => false, 'mensaje' => "No se encontraron registros con el documento '{$doc}'."];
        }

        return [
            'encontrado'         => true,
            'documento'          => $doc,
            'total_contratos'    => count($contratos),
            'total_afiliaciones' => count($afiliaciones),
            'contratos'          => $contratos,
            'afiliaciones'       => $afiliaciones,
        ];
    }

    private function toolCruceContratoAfiliacion(array $input): array
    {
        $qC = Contrato::whereNotNull('nombre_persona_natural');
        if (! empty($input['vigencia']))           $qC->where('vigencia', $input['vigencia']);
        if (! empty($input['dependencia_nombre'])) {
            $dep = $input['dependencia_nombre'];
            $qC->where(function ($q2) use ($dep) {
                $q2->whereHas('dependencia', fn ($d) => $d->where('nombre', 'like', "%{$dep}%"))
                   ->orWhere('dependencia_contrato', 'like', "%{$dep}%");
            });
        }

        $contratos = $qC->get(['id', 'numero_contrato', 'vigencia', 'nombre_persona_natural', 'cedula', 'valor_contrato', 'dependencia_id', 'dependencia_contrato']);

        // Documentos con afiliación
        $docsConAfiliacion = Afiliacion::pluck('numero_documento')->unique()->toArray();

        $conAfiliacion    = [];
        $sinAfiliacion    = [];

        foreach ($contratos as $c) {
            $cedula = $c->cedula;
            $entry = [
                'numero'      => $c->numero_contrato,
                'vigencia'    => $c->vigencia,
                'contratista' => $c->nombre_persona_natural,
                'cedula'      => $cedula,
                'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
            ];
            if ($cedula && in_array($cedula, $docsConAfiliacion)) {
                $conAfiliacion[] = $entry;
            } else {
                $sinAfiliacion[] = $entry;
            }
        }

        return [
            'total_contratos'    => count($contratos),
            'con_afiliacion'     => count($conAfiliacion),
            'sin_afiliacion'     => count($sinAfiliacion),
            'porcentaje_cobertura'=> count($contratos) > 0
                ? round(count($conAfiliacion) / count($contratos) * 100, 1) . '%'
                : '0%',
            'contratos_sin_afiliacion' => array_slice($sinAfiliacion, 0, 30),
        ];
    }
}
