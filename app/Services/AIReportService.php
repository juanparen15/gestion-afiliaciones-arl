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
                $result = $this->ejecutar($fn['name'], $fn['args'] ?? []);

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
               'Tienes acceso a información real sobre contratos SECOP y afiliaciones ARL. ' .
               'Responde siempre en español, de forma clara, concisa y profesional. ' .
               'Usa las herramientas disponibles para consultar datos antes de responder. ' .
               'Cuando presentes listas usa formato estructurado con viñetas. ' .
               'Si la pregunta no está relacionada con contratos o afiliaciones, indícalo amablemente.';
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
            'afiliaciones_proximas_vencer' => $this->toolAfiliacionesProximasVencer($input),
            default                        => ['error' => "Herramienta '{$nombre}' no encontrada."],
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
