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

               "REGLAS DE USO DE HERRAMIENTAS:\n" .
               "- Para preguntas sobre un trimestre específico, usa el parámetro trimestre (1=ene-mar, 2=abr-jun, 3=jul-sep, 4=oct-dic).\n" .
               "- Para el año actual usa vigencia={$anioActual}.\n" .
               "- Para listar dependencias disponibles usa la herramienta listar_dependencias.\n" .
               "- Para detalle de afiliaciones (por dependencia, año, trimestre, ARL) usa afiliaciones_detallado.\n" .
               "- Para detalle de contratos SECOP con filtros múltiples usa contratos_detallado.\n" .
               "- Cuando el usuario pide 'más detalle', 'detalle de cada contrato', 'listado', 'uno a uno' o 'qué contratos son':\n" .
               "  USA SIEMPRE con_detalle: true en contratos_detallado para obtener el listado individual.\n" .
               "- El campo con_detalle devuelve número, contratista, clase, objeto, valor, fechas, fuente y dependencia de cada contrato.";
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
                'description' => 'Busca un contratista por nombre y retorna sus contratos SECOP y afiliaciones ARL.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'nombre'  => ['type' => 'string',  'description' => 'Nombre parcial del contratista. Requerido.'],
                        'limite'  => ['type' => 'integer', 'description' => 'Máximo resultados por entidad. Por defecto 5.'],
                    ],
                    'required' => ['nombre'],
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
        $nombre = $input['nombre'] ?? '';
        $limite = (int) ($input['limite'] ?? 5);

        $contratos = Contrato::with('dependencia')
            ->where(function ($q) use ($nombre) {
                $q->where('nombre_persona_natural', 'like', "%{$nombre}%")
                  ->orWhere('nombre_persona_juridica', 'like', "%{$nombre}%");
            })
            ->latest()->limit($limite)->get()
            ->map(fn ($c) => [
                'numero'        => $c->numero_contrato,
                'tipo_contrato' => $c->tipo_contrato,
                'estado'        => $c->estado,
                'vigencia'      => $c->vigencia,
                'valor'         => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'dependencia'   => $c->dependencia?->nombre,
                'objeto'        => str()->limit($c->objeto ?? '', 80),
            ])->toArray();

        $afiliaciones = Afiliacion::with('dependencia')
            ->where('nombre_contratista', 'like', "%{$nombre}%")
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
}
