<?php

namespace App\Services;

use App\Models\Afiliacion;
use App\Models\Contrato;
use Illuminate\Support\Facades\Http;

class AIReportService
{
    private string $apiKey;
    private string $model;
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key', '');
        $this->model  = config('services.anthropic.model', 'claude-opus-4-6');
    }

    public function consultar(string $pregunta): array
    {
        if (empty($this->apiKey)) {
            return ['error' => 'ANTHROPIC_API_KEY no está configurada en el servidor.'];
        }

        $messages   = [['role' => 'user', 'content' => $pregunta]];
        $tools      = $this->tools();
        $headers    = [
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ];

        // Primera llamada
        $res = Http::withHeaders($headers)->post(self::API_URL, [
            'model'      => $this->model,
            'max_tokens' => 2048,
            'system'     => $this->systemPrompt(),
            'tools'      => $tools,
            'messages'   => $messages,
        ]);

        if (! $res->successful()) {
            return ['error' => 'Error al contactar la IA: ' . $res->body()];
        }

        $data = $res->json();

        // Si Claude quiere usar herramientas → ejecutarlas y llamar de nuevo
        if (($data['stop_reason'] ?? '') === 'tool_use') {
            $toolResults = [];

            foreach ($data['content'] as $block) {
                if ($block['type'] === 'tool_use') {
                    $resultado = $this->ejecutar($block['name'], $block['input'] ?? []);
                    $toolResults[] = [
                        'type'        => 'tool_result',
                        'tool_use_id' => $block['id'],
                        'content'     => json_encode($resultado, JSON_UNESCAPED_UNICODE),
                    ];
                }
            }

            $messages[] = ['role' => 'assistant', 'content' => $data['content']];
            $messages[] = ['role' => 'user',      'content' => $toolResults];

            $res2 = Http::withHeaders($headers)->post(self::API_URL, [
                'model'      => $this->model,
                'max_tokens' => 2048,
                'system'     => $this->systemPrompt(),
                'tools'      => $tools,
                'messages'   => $messages,
            ]);

            if (! $res2->successful()) {
                return ['error' => 'Error en segunda llamada a la IA.'];
            }

            $data = $res2->json();
        }

        // Extraer texto final
        $texto = collect($data['content'] ?? [])
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        return [
            'respuesta' => $texto,
            'tokens'    => $data['usage'] ?? null,
        ];
    }

    // ─── System prompt ───────────────────────────────────────────────────────

    private function systemPrompt(): string
    {
        return 'Eres un asistente de análisis de datos del Sistema de Gestión ARL de la Alcaldía Municipal de Puerto Boyacá. ' .
               'Tienes acceso a información real sobre contratos SECOP y afiliaciones ARL. ' .
               'Responde siempre en español, de forma clara, concisa y profesional. ' .
               'Usa las herramientas disponibles para consultar datos antes de responder. ' .
               'Cuando presentes listas usa formato estructurado con viñetas. ' .
               'Si la pregunta no está relacionada con contratos o afiliaciones, indícalo amablemente.';
    }

    // ─── Definición de herramientas ──────────────────────────────────────────

    private function tools(): array
    {
        return [
            [
                'name'         => 'resumen_contratos',
                'description'  => 'Resumen general de contratos SECOP: totales, estados y valor. Acepta filtro opcional por vigencia (año).',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia' => ['type' => 'string', 'description' => 'Año, ej: 2024. Opcional.'],
                    ],
                ],
            ],
            [
                'name'         => 'contratos_por_dependencia',
                'description'  => 'Cantidad y valor total de contratos agrupados por dependencia.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia' => ['type' => 'string', 'description' => 'Año. Opcional.'],
                        'estado'   => ['type' => 'string', 'description' => 'Estado del contrato. Opcional.'],
                    ],
                ],
            ],
            [
                'name'         => 'contratos_proximos_vencer',
                'description'  => 'Lista contratos SECOP activos que vencen en los próximos N días (fecha efectiva con adiciones/prórrogas).',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'dias' => ['type' => 'integer', 'description' => 'Días de anticipación. Por defecto 30.'],
                    ],
                ],
            ],
            [
                'name'         => 'contratos_vencidos',
                'description'  => 'Contratos en estado TERMINADO, opcionalmente filtrados por vigencia o dependencia.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'vigencia'            => ['type' => 'string',  'description' => 'Año. Opcional.'],
                        'dependencia_nombre'  => ['type' => 'string',  'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'         => 'top_contratistas',
                'description'  => 'Contratistas con más contratos registrados.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'limite'   => ['type' => 'integer', 'description' => 'Cantidad de resultados. Por defecto 10.'],
                        'vigencia' => ['type' => 'string',  'description' => 'Año. Opcional.'],
                    ],
                ],
            ],
            [
                'name'         => 'resumen_afiliaciones',
                'description'  => 'Totales de afiliaciones ARL por estado y dependencia.',
                'input_schema' => [
                    'type'       => 'object',
                    'properties' => [
                        'dependencia_nombre' => ['type' => 'string', 'description' => 'Nombre parcial de la dependencia. Opcional.'],
                    ],
                ],
            ],
            [
                'name'         => 'afiliaciones_proximas_vencer',
                'description'  => 'Afiliaciones ARL validadas próximas a vencer.',
                'input_schema' => [
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
        if (! empty($input['vigencia'])) {
            $q->where('vigencia', $input['vigencia']);
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
