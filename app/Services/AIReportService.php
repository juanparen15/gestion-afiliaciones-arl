<?php

namespace App\Services;

use App\Models\Afiliacion;
use App\Models\Contrato;
use Illuminate\Support\Facades\Http;

class AIReportService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key', '');
        $this->model  = config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function consultar(string $pregunta): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'GEMINI_API_KEY no está configurada en el servidor.'];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $contents = [['role' => 'user', 'parts' => [['text' => $pregunta]]]];

        $payload = [
            'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
            'tools'              => [['function_declarations' => $this->tools()]],
            'contents'           => $contents,
        ];

        // Primera llamada (con reintentos en 503)
        $res = $this->postWithRetry($url, $payload);

        if (! $res->successful()) {
            return ['error' => 'Error al contactar Gemini: ' . $res->body()];
        }

        $data = $res->json();

        // Si Gemini quiere usar herramientas → ejecutarlas y llamar de nuevo
        // Nota: PHP decodifica args:{} como [] (array vacío); hay que revertirlo a objeto
        $parts = array_map(function ($p) {
            if (isset($p['functionCall']['args']) && $p['functionCall']['args'] === []) {
                $p['functionCall']['args'] = new \stdClass();
            }
            return $p;
        }, $data['candidates'][0]['content']['parts'] ?? []);
        $functionCalls = array_filter($parts, fn ($p) => isset($p['functionCall']));

        if (! empty($functionCalls)) {
            // Agregar respuesta del modelo al historial
            $contents[] = ['role' => 'model', 'parts' => $parts];

            // Ejecutar herramientas y construir respuestas
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

            $res2 = $this->postWithRetry($url, $payload);

            if (! $res2->successful()) {
                return ['error' => 'Error en segunda llamada a Gemini: ' . $res2->body()];
            }

            $data = $res2->json();
        }

        // Extraer texto final
        $texto = collect($data['candidates'][0]['content']['parts'] ?? [])
            ->filter(fn ($p) => isset($p['text']))
            ->pluck('text')
            ->implode('');

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
                sleep($attempt * 2); // 2s, 4s antes del siguiente intento
            }
        } while ($attempt < $maxAttempts);

        return $res;
    }

    // ─── System prompt ────────────────────────────────────────────────────────

    private function systemPrompt(): string
    {
        return 'Eres un asistente de análisis de datos del Sistema de Gestión ARL de la Alcaldía Municipal de Puerto Boyacá. ' .
               'Tienes acceso a información real sobre contratos SECOP y afiliaciones ARL mediante herramientas. ' .
               'SIEMPRE usa las herramientas disponibles para consultar datos antes de responder. ' .
               'NUNCA digas que no tienes acceso a información sin intentar consultar la herramienta adecuada primero. ' .
               'Responde siempre en español, de forma clara, concisa y profesional. ' .
               'Cuando presentes listas usa formato estructurado con viñetas o numeración. ' .
               'Si la pregunta no está relacionada con contratos o afiliaciones, indícalo amablemente. ' .
               "\n\nCONOCIMIENTO DE LA BASE DE DATOS:" .
               "\n- La columna 'modalidad' contiene SOLO códigos cortos (CD-CPS, LIC-006, etc.), NO descripciones de tipo de contrato." .
               "\n- Para filtrar por tipo de contrato usa SIEMPRE el parámetro 'tipo_contrato' o 'tipos_contrato' en contratos_detallado." .
               "\n- Valores reales de tipo_contrato en la BD:" .
               "\n  * 'C1 Prestación de Servicios Profesionales' → para contratos de servicios profesionales" .
               "\n  * 'C2 Prestación de Servicios de Apoyo a la Gestión' → para apoyo a la gestión, apoyo técnico, apoyo tecnológico" .
               "\n  * 'NO APLICA' → contratos de otro tipo (arrendamiento, obra, suministro, etc.)" .
               "\n- 'Prestación de servicios de apoyo técnicos y tecnológicos' NO es un tipo separado; se registra como 'C2 Prestación de Servicios de Apoyo a la Gestión'." .
               "\n- Para preguntas sobre primer trimestre usa trimestre=1 con el año correspondiente.";
    }

    // ─── Definición de herramientas (formato Gemini) ──────────────────────────

    private function tools(): array
    {
        return [
            [
                'name'        => 'resumen_contratos',
                'description' => 'Resumen general de contratos SECOP: totales, estados y valor. Acepta filtro opcional por vigencia (año).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia' => ['type' => 'string', 'description' => 'Año, ej: 2024. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_por_dependencia',
                'description' => 'Cantidad y valor total de contratos agrupados por dependencia.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia' => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'estado'   => ['type' => 'string', 'description' => 'Estado del contrato. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_proximos_vencer',
                'description' => 'Lista contratos SECOP activos que vencen en los próximos N días (fecha efectiva con adiciones/prórrogas).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dias' => ['type' => 'integer', 'description' => 'Días de anticipación. Por defecto 30.'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_vencidos',
                'description' => 'Contratos en estado TERMINADO, opcionalmente filtrados por vigencia o dependencia.',
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
                'description' => 'Contratistas con más contratos registrados.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'limite'   => ['type' => 'integer', 'description' => 'Cantidad de resultados. Por defecto 10.'],
                        'vigencia' => ['type' => 'string',  'description' => 'Año. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'resumen_afiliaciones',
                'description' => 'Totales de afiliaciones ARL por estado y dependencia.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'afiliaciones_proximas_vencer',
                'description' => 'Afiliaciones ARL validadas próximas a vencer.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'dias' => ['type' => 'integer', 'description' => 'Días de anticipación. Por defecto 30.'],
                    ],
                ],
            ],
            [
                'name'        => 'top_contratistas_afiliaciones',
                'description' => 'Contratistas con más afiliaciones ARL registradas, con su valor total y estados.',
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
                        'estado' => ['type' => 'string', 'description' => 'Filtrar por estado. Opcional.'],
                    ],
                ],
            ],
            [
                'name'        => 'estadisticas_afiliaciones',
                'description' => 'Estadísticas detalladas de afiliaciones: por ARL, por tipo de riesgo, por EPS, promedios de valor.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'agrupar_por' => ['type' => 'string', 'description' => 'Campo de agrupación: "arl", "tipo_riesgo", "eps", "estado". Por defecto "arl".'],
                    ],
                ],
            ],
            [
                'name'        => 'contratos_detallado',
                'description' => 'Consulta detallada de contratos con filtros por tipo_contrato (descripción del tipo), trimestre y fuente de financiación. ' .
                                 'IMPORTANTE: usa tipo_contrato para filtrar por "Prestación de Servicios Profesionales" o "Apoyo a la Gestión". ' .
                                 'Los valores reales en la BD son: "C1 Prestación de Servicios Profesionales" y "C2 Prestación de Servicios de Apoyo a la Gestión". ' .
                                 'El campo modalidad contiene SOLO códigos (CD-CPS, LIC-006, etc.), NO descripciones. ' .
                                 'Incluye resumen de prórrogas, adiciones y fuentes de financiación.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'        => ['type' => 'string',  'description' => 'Año. Ej: "2026". Opcional.'],
                        'tipo_contrato'   => ['type' => 'string',  'description' => 'Texto parcial del tipo de contrato (columna tipo_contrato). Ej: "Apoyo a la Gestión", "Servicios Profesionales". Hace búsqueda LIKE. Opcional.'],
                        'tipos_contrato'  => ['type' => 'array', 'items' => ['type' => 'string'],
                                              'description' => 'Lista de tipos de contrato para filtrar simultáneamente con OR. Ej: ["Apoyo a la Gestión","Servicios Profesionales"]. Opcional.'],
                        'modalidad'       => ['type' => 'string',  'description' => 'Código de modalidad (NO texto descriptivo). Ej: "CD-CPS". Opcional.'],
                        'trimestre'       => ['type' => 'integer', 'description' => 'Trimestre por fecha_inicio: 1=ene-mar, 2=abr-jun, 3=jul-sep, 4=oct-dic. Opcional.'],
                        'agrupar_por'     => ['type' => 'string',  'description' => 'Agrupar por: "tipo_contrato", "fuente_financiacion", "dependencia". Por defecto "tipo_contrato".'],
                        'con_detalle'     => ['type' => 'boolean', 'description' => 'Si true devuelve listado de contratos individuales (máx 50). Por defecto false.'],
                    ],
                ],
            ],
            [
                'name'        => 'buscar_contratista',
                'description' => 'Busca un contratista por nombre y retorna sus contratos y afiliaciones registradas.',
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
            'resumen_contratos'            => $this->toolResumenContratos($input),
            'contratos_por_dependencia'    => $this->toolContratosPorDependencia($input),
            'contratos_proximos_vencer'    => $this->toolContratosProximosVencer($input),
            'contratos_vencidos'           => $this->toolContratosVencidos($input),
            'top_contratistas'             => $this->toolTopContratistas($input),
            'resumen_afiliaciones'         => $this->toolResumenAfiliaciones($input),
            'afiliaciones_proximas_vencer'  => $this->toolAfiliacionesProximasVencer($input),
            'contratos_detallado'           => $this->toolContratosDetallado($input),
            'top_contratistas_afiliaciones' => $this->toolTopContratistasAfiliaciones($input),
            'afiliaciones_por_dependencia'  => $this->toolAfiliacionesPorDependencia($input),
            'estadisticas_afiliaciones'     => $this->toolEstadisticasAfiliaciones($input),
            'buscar_contratista'            => $this->toolBuscarContratista($input),
            default                         => ['error' => "Herramienta '{$nombre}' no encontrada."],
        };
    }

    // ─── Implementaciones ─────────────────────────────────────────────────────

    private function toolResumenContratos(array $input): array
    {
        $q = Contrato::query();
        if (! empty($input['vigencia'])) $q->where('vigencia', $input['vigencia']);

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

        $total   = (clone $q)->count();
        $estados = (clone $q)
            ->selectRaw('estado, count(*) as cantidad')
            ->groupBy('estado')->orderByDesc('cantidad')->get()
            ->map(fn ($r) => ['estado' => $r->estado, 'cantidad' => $r->cantidad])
            ->toArray();

        return ['total' => $total, 'por_estado' => $estados];
    }

    private function toolContratosDetallado(array $input): array
    {
        $q = Contrato::with('dependencia');

        // Vigencia
        if (! empty($input['vigencia'])) {
            $q->where('vigencia', $input['vigencia']);
        }

        // Tipo contrato (descripción): una o varias — usa LIKE sobre tipo_contrato
        $tiposContrato = $input['tipos_contrato'] ?? [];
        if (! empty($input['tipo_contrato'])) {
            $tiposContrato[] = $input['tipo_contrato'];
        }
        if (! empty($tiposContrato)) {
            $q->where(function ($sub) use ($tiposContrato) {
                foreach ($tiposContrato as $t) {
                    $sub->orWhere('tipo_contrato', 'like', "%{$t}%");
                }
            });
        }

        // Modalidad (código): filtro por código si se proporciona
        if (! empty($input['modalidad'])) {
            $q->where('modalidad', 'like', '%' . $input['modalidad'] . '%');
        }

        // Trimestre → rango de fecha_inicio
        if (! empty($input['trimestre'])) {
            $t = (int) $input['trimestre'];
            $year = $input['vigencia'] ?? now()->year;
            [$mesInicio, $mesFin] = match ($t) {
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
                default => [1, 12],
            };
            $desde = \Carbon\Carbon::create($year, $mesInicio, 1)->startOfDay();
            $hasta = \Carbon\Carbon::create($year, $mesFin, 1)->endOfMonth()->endOfDay();
            $q->whereBetween('fecha_inicio', [$desde, $hasta]);
        }

        $contratos = $q->orderBy('fecha_inicio')->get();

        // Resumen general
        $total      = $contratos->count();
        $valorTotal = $contratos->sum('valor_contrato');

        // Prórrogas
        $conProrroga = $contratos->filter(fn ($c) =>
            $c->fecha_prorroga_1 || $c->fecha_prorroga_2 || $c->fecha_prorroga_3
        );

        // Adiciones
        $conAdicion = $contratos->filter(fn ($c) => $c->tieneAdiciones());

        // Agrupación
        $campoAgrupar = $input['agrupar_por'] ?? 'tipo_contrato';
        $agrupacion = $contratos->groupBy(fn ($c) => match ($campoAgrupar) {
            'fuente_financiacion' => $c->fuente_financiacion ?? 'No especificada',
            'dependencia'         => $c->dependencia?->nombre ?? 'Sin dependencia',
            'modalidad'           => $c->modalidad ?? 'Sin modalidad',
            default               => $c->tipo_contrato ?? 'Sin tipo',
        })->map(fn ($grupo, $key) => [
            $campoAgrupar => $key,
            'cantidad'    => $grupo->count(),
            'valor'       => '$' . number_format($grupo->sum('valor_contrato'), 0, ',', '.'),
        ])->sortByDesc('cantidad')->values()->toArray();

        // Fuentes de financiación (siempre incluidas)
        $fuentes = $contratos->groupBy(fn ($c) => $c->fuente_financiacion ?? 'No especificada')
            ->map(fn ($g, $k) => [
                'fuente'   => $k,
                'cantidad' => $g->count(),
                'valor'    => '$' . number_format($g->sum('valor_contrato'), 0, ',', '.'),
            ])->sortByDesc('cantidad')->values()->toArray();

        $resultado = [
            'total_contratos'      => $total,
            'valor_total'          => '$' . number_format($valorTotal, 0, ',', '.'),
            'con_prorroga'         => $conProrroga->count(),
            'con_adicion'          => $conAdicion->count(),
            'agrupacion'           => $agrupacion,
            'fuentes_financiacion' => $fuentes,
        ];

        // Detalle individual si se solicita
        if (! empty($input['con_detalle'])) {
            $resultado['contratos'] = $contratos->take(50)->map(fn ($c) => [
                'numero'        => $c->numero_contrato,
                'contratista'   => $c->getNombreContratista(),
                'tipo_contrato' => $c->tipo_contrato,
                'modalidad'     => $c->modalidad,
                'objeto'        => str()->limit($c->objeto ?? '', 80),
                'valor'         => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'fecha_inicio'  => $c->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'     => $c->fechaEfectivaCierre()?->format('d/m/Y'),
                'fuente'        => $c->fuente_financiacion,
                'prorroga'      => $c->fecha_prorroga_1 ? 'Sí' : 'No',
                'dependencia'   => $c->dependencia?->nombre,
            ])->toArray();
        }

        return $resultado;
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
            'contratista'   => $r->nombre_contratista,
            'afiliaciones'  => $r->cantidad,
            'valor_total'   => '$' . number_format($r->valor_total ?? 0, 0, ',', '.'),
        ])->toArray();
    }

    private function toolAfiliacionesPorDependencia(array $input): array
    {
        $q = Afiliacion::with('dependencia')
            ->selectRaw('dependencia_id, count(*) as cantidad, sum(valor_contrato) as valor')
            ->groupBy('dependencia_id');

        if (! empty($input['estado'])) $q->where('estado', $input['estado']);

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

        $label = match ($campo) {
            'tipo_riesgo' => 'tipo_riesgo',
            'eps'         => 'eps',
            'estado'      => 'estado',
            default       => 'arl',
        };

        return Afiliacion::selectRaw("{$campo} as agrupacion, count(*) as cantidad, avg(ibc) as ibc_promedio, sum(valor_contrato) as valor_total")
            ->groupBy($campo)
            ->orderByDesc('cantidad')
            ->get()
            ->map(fn ($r) => [
                $label       => $r->agrupacion ?? 'No especificado',
                'cantidad'   => $r->cantidad,
                'ibc_prom'   => '$' . number_format($r->ibc_promedio ?? 0, 0, ',', '.'),
                'valor_total'=> '$' . number_format($r->valor_total ?? 0, 0, ',', '.'),
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
            ->latest()
            ->limit($limite)
            ->get()
            ->map(fn ($c) => [
                'numero'      => $c->numero_contrato,
                'estado'      => $c->estado,
                'vigencia'    => $c->vigencia,
                'valor'       => '$' . number_format($c->valor_contrato ?? 0, 0, ',', '.'),
                'dependencia' => $c->dependencia?->nombre,
                'objeto'      => str()->limit($c->objeto_contractual ?? '', 80),
            ])->toArray();

        $afiliaciones = Afiliacion::with('dependencia')
            ->where('nombre_contratista', 'like', "%{$nombre}%")
            ->latest()
            ->limit($limite)
            ->get()
            ->map(fn ($a) => [
                'estado'      => $a->estado,
                'arl'         => $a->nombre_arl,
                'dependencia' => $a->dependencia?->nombre,
                'fecha_inicio'=> $a->fecha_inicio?->format('d/m/Y'),
                'fecha_fin'   => $a->fecha_fin?->format('d/m/Y'),
                'valor'       => '$' . number_format($a->valor_contrato ?? 0, 0, ',', '.'),
            ])->toArray();

        return [
            'busqueda'           => $nombre,
            'total_contratos'    => count($contratos),
            'total_afiliaciones' => count($afiliaciones),
            'contratos'          => $contratos,
            'afiliaciones'       => $afiliaciones,
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
}
