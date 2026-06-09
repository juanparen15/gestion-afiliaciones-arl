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

    /** Equivalencias manuales: nombre legacy normalizado => area_id ARL (cuando es un ÁREA real). */
    private array $aliasAreas = [
        'area de almacen' => 9,
        'area de archivo' => 12,
        'area de vivienda' => 26,
        'area de salud' => 25,
        'area de cultura' => 21,
        'cuerpo de bomberos' => 13,
    ];

    /** Equivalencias manuales: nombre legacy normalizado => dependencia_id ARL (cuando es una DEPENDENCIA/secretaría). */
    private array $aliasDependencias = [
        'umata' => 14,
        'biblioteca publica municipal' => 18,
        'secretaria de planeacion' => 11,
        'secretaria de general y de servicios administrativos' => 8,
        'secretaria de gobierno' => 9,
        'secretaria de desarrollo' => 10,
        'secretaria de obras publicas' => 15,
        'secretaria de hacienda' => 12,
        'despacho alcalde' => 6,
        'inspeccion de transito y transporte' => 13,
        'control interno' => 7,
        'area de prensa y comunicaciones' => 8,
    ];

    public function handle(): int
    {
        $legacy = $this->option('connection');
        $this->info("Importando desde conexión: {$legacy}");

        $this->importarCatalogos($legacy);
        $mapaUbicacion = $this->construirMapaUbicacion($legacy);
        $mapaUsers = $this->construirMapaUsuarios($legacy, $mapaUbicacion);
        $this->importarPlanes($legacy, $mapaUbicacion, $mapaUsers);
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

    /**
     * Resuelve cada área legacy a una ubicación ARL: ['area_id' => ?, 'dependencia_id' => ?].
     * Orden: match por nombre de área → alias de área → alias de dependencia → sin match.
     * Si resuelve a un área, deriva la dependencia desde esa área.
     */
    private function construirMapaUbicacion(string $legacy): array
    {
        $arlAreas = DB::table('areas')->get()->keyBy(fn ($a) => $this->norm($a->nombre));
        $depPorArea = DB::table('areas')->pluck('dependencia_id', 'id');

        $mapa = [];
        foreach (DB::connection($legacy)->table('areas')->get() as $a) {
            $key = $this->norm($a->nomarea);
            $areaId = null;
            $depId = null;

            if (isset($arlAreas[$key])) {
                $areaId = $arlAreas[$key]->id;
            } elseif (isset($this->aliasAreas[$key])) {
                $areaId = $this->aliasAreas[$key];
            } elseif (isset($this->aliasDependencias[$key])) {
                $depId = $this->aliasDependencias[$key];
            } else {
                $this->reporte['areas_sin_match'][] = $a->nomarea;
            }

            if ($areaId !== null) {
                $depId = $depPorArea[$areaId] ?? null;
            }

            $mapa[$a->id] = ['area_id' => $areaId, 'dependencia_id' => $depId];
        }

        return $mapa;
    }

    private function construirMapaUsuarios(string $legacy, array $mapaUbicacion): array
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
            $ubic = $mapaUbicacion[$u->areas_id] ?? ['area_id' => null, 'dependencia_id' => null];
            $nuevo = User::create([
                'name' => $u->name,
                'email' => $u->email,
                'password' => Hash::make(Str::random(16)),
                'area_id' => $ubic['area_id'],
                'dependencia_id' => $ubic['dependencia_id'],
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

    private function importarPlanes(string $legacy, array $mapaUbicacion, array $mapaUsers): void
    {
        $total = DB::connection($legacy)->table('planadquisiciones')->count();
        $this->line("Planes: {$total} filas");

        DB::connection($legacy)->table('planadquisiciones')->orderBy('id')->chunk(500, function ($filas) use ($mapaUbicacion, $mapaUsers) {
            $data = $filas->map(function ($p) use ($mapaUbicacion, $mapaUsers) {
                $d = (array) $p;
                $ubic = $mapaUbicacion[$p->area_id] ?? ['area_id' => null, 'dependencia_id' => null];
                $d['area_id'] = $ubic['area_id'];
                $d['dependencia_id'] = $ubic['dependencia_id'];
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
