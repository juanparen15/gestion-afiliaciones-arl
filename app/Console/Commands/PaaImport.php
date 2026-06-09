<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PaaImport extends Command
{
    protected $signature = 'paa:import {--connection=paa_legacy}';
    protected $description = 'Importa los datos del PAA legacy a gestion_arl con remapeo de áreas y usuarios';

    private array $catalogos = [
        'segmentos', 'familias', 'clases', 'productos',
        'estadovigencias', 'meses', 'modalidades', 'intervalos',
        'vigenfuturas', 'tipozonas', 'tipoprocesos', 'tipoadquisiciones',
        'requiproyectos', 'fuentes', 'tipoprioridades', 'requipoais',
    ];

    private array $reporte = ['areas_sin_match' => [], 'usuarios_creados' => [], 'usuarios_sin_match' => []];

    public function handle(): int
    {
        $legacy = $this->option('connection');
        $this->info("Importando desde conexión: {$legacy}");

        $this->importarCatalogos($legacy);
        $mapaAreas = $this->construirMapaAreas($legacy);
        $mapaUsers = $this->construirMapaUsuarios($legacy, $mapaAreas);
        $this->importarPlanes($legacy, $mapaAreas, $mapaUsers);
        $this->importarPivote($legacy);
        $this->imprimirReporte();

        return self::SUCCESS;
    }

    private function importarCatalogos(string $legacy): void
    {
        foreach ($this->catalogos as $tabla) {
            $total = DB::connection($legacy)->table($tabla)->count();
            $this->line("Catálogo {$tabla}: {$total} filas");

            DB::connection($legacy)->table($tabla)->orderBy('id')->chunk(1000, function ($filas) use ($tabla) {
                $data = array_map(fn ($f) => (array) $f, $filas->all());
                DB::table($tabla)->upsert($data, ['id']);
            });
        }
    }

    private function construirMapaAreas(string $legacy): array
    {
        $arl = DB::table('areas')->get()->keyBy(fn ($a) => $this->norm($a->nombre));
        $mapa = [];
        foreach (DB::connection($legacy)->table('areas')->get() as $a) {
            $key = $this->norm($a->nomarea);
            if (isset($arl[$key])) {
                $mapa[$a->id] = $arl[$key]->id;
            } else {
                $this->reporte['areas_sin_match'][] = $a->nomarea;
            }
        }
        return $mapa;
    }

    private function construirMapaUsuarios(string $legacy, array $mapaAreas): array
    {
        $arl = DB::table('users')->get()->keyBy(fn ($u) => strtolower(trim($u->email)));
        $userCols = Schema::getColumnListing('users');
        $mapa = [];
        foreach (DB::connection($legacy)->table('users')->get() as $u) {
            $email = strtolower(trim($u->email));
            if (isset($arl[$email])) {
                $mapa[$u->id] = $arl[$email]->id;
                continue;
            }
            $nuevo = User::create([
                'name' => $u->name,
                'email' => $u->email,
                'password' => Hash::make(Str::random(16)),
                'area_id' => $mapaAreas[$u->areas_id] ?? null,
            ]);
            $extra = array_filter([
                'apellido' => $u->apellido ?? null,
                'telefono' => $u->telefono ?? null,
                'documento' => $u->documento ?? null,
            ], fn ($v) => $v !== null);
            $extra = array_intersect_key($extra, array_flip($userCols));
            if ($extra) {
                DB::table('users')->where('id', $nuevo->id)->update($extra);
            }
            $this->asignarRolBasico($nuevo);
            $mapa[$u->id] = $nuevo->id;
            $this->reporte['usuarios_creados'][] = $u->email;
        }
        return $mapa;
    }

    private function asignarRolBasico(User $user): void
    {
        // Asigna un rol básico si existe; no aborta si no hay roles.
        // Roles existentes en producción: Administrador, Dependencia, SSST, super_admin
        // 'Dependencia' es el rol básico de usuario no-administrador.
        try {
            $rol = config('paa.import_role', 'Dependencia');
            if (method_exists($user, 'assignRole')) {
                $user->assignRole($rol);
            }
        } catch (\Throwable $e) {
            $this->warn("No se pudo asignar rol a {$user->email}: {$e->getMessage()}");
        }
    }

    private function importarPlanes(string $legacy, array $mapaAreas, array $mapaUsers): void
    {
        $total = DB::connection($legacy)->table('planadquisiciones')->count();
        $this->line("Planes: {$total} filas");

        DB::connection($legacy)->table('planadquisiciones')->orderBy('id')->chunk(500, function ($filas) use ($mapaAreas, $mapaUsers) {
            $data = $filas->map(function ($p) use ($mapaAreas, $mapaUsers) {
                $d = (array) $p;
                $d['area_id'] = $mapaAreas[$p->area_id] ?? null;
                $d['user_id'] = $mapaUsers[$p->user_id] ?? null;
                return $d;
            })->all();

            DB::table('planadquisiciones')->upsert($data, ['id']);
        });
    }

    private function importarPivote(string $legacy): void
    {
        $total = DB::connection($legacy)->table('planadquisicione_producto')->count();
        $this->line("Pivote producto: {$total} filas");

        $omitidas = 0;

        DB::connection($legacy)->table('planadquisicione_producto')->orderBy('id')->chunk(1000, function ($filas) use (&$omitidas) {
            $data = [];
            foreach ($filas as $row) {
                // Las filas legacy sin plan asociado son huérfanas: se omiten.
                if (is_null($row->planadquisicione_id)) {
                    $omitidas++;
                    continue;
                }
                $data[] = (array) $row;
            }

            if ($data) {
                DB::table('planadquisicione_producto')->upsert($data, ['id']);
            }
        });

        if ($omitidas > 0) {
            $this->warn("Pivote: {$omitidas} filas omitidas por no tener plan asociado (planadquisicione_id null).");
        }
    }

    private function norm(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = strtr($v, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        return preg_replace('/\s+/', ' ', $v);
    }

    private function imprimirReporte(): void
    {
        $path = storage_path('logs/paa-import-' . date('Ymd-His') . '.log');
        $txt = "REPORTE IMPORTACIÓN PAA\n"
            . "Áreas sin match (" . count($this->reporte['areas_sin_match']) . "): " . implode(', ', $this->reporte['areas_sin_match']) . "\n"
            . "Usuarios creados (" . count($this->reporte['usuarios_creados']) . "): " . implode(', ', $this->reporte['usuarios_creados']) . "\n";
        file_put_contents($path, $txt);
        $this->warn("Áreas sin match: " . count($this->reporte['areas_sin_match']));
        $this->warn("Usuarios creados: " . count($this->reporte['usuarios_creados']));
        $this->info("Reporte guardado en: {$path}");
    }
}
