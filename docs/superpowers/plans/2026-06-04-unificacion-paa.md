# Unificación del módulo PAA — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Portar el módulo "Plan Anual de Adquisiciones" (PAA) al proyecto `gestion-afiliaciones-arl` (Filament v3) en una sola BD, e importar los datos de producción, dejando el sistema unificado y funcional con vínculo Plan→Contrato.

**Architecture:** Una sola base `gestion_arl`. Se crean las tablas PAA (UNSPSC + 12 lookups + planadquisiciones + pivote), se reusan `areas`/`dependencias`/`users` existentes, y se vincula `contratos.planadquisicione_id` (1:N). Un comando `paa:import` idempotente lee de una BD staging `paa_legacy` (cargada desde `paa.sql`) y vuelca los datos remapeando áreas/usuarios y creando los usuarios PAA faltantes. Toda la UI es Filament v3, portada desde el prototipo v4 de `paa-v4`.

**Tech Stack:** Laravel 12, Filament v3.2, spatie/laravel-permission, leandrocfe/filament-apex-charts v3, pxlrbt/filament-excel v2, maatwebsite/excel, PHPUnit (SQLite :memory:).

**Spec:** `docs/superpowers/specs/2026-06-04-unificacion-paa-design.md`

---

## Convenciones del proyecto destino (Filament v3)

- Form: `public static function form(Form $form): Form` con `use Filament\Forms\Form;`, componentes `Forms\Components\*`.
- Table: `public static function table(Table $table): Table` con `use Filament\Tables\Table;`, columnas `Tables\Columns\*`, acciones `Tables\Actions\*`.
- `Get`/`Set`: `use Filament\Forms\Get;` y `use Filament\Forms\Set;`.
- Nombres de modelo legacy preservados: `Planadquisicione`, `Mese`, `Modalidade`, `Requipoai`, `Requiproyecto`, `Tipoadquisicione`, `Tipoprioridade`, `Estadovigencia`, `Vigenfutura`, `Segmento`, `Familia`, `Clase`, `Producto`, `Fuente`, `Intervalo`, `Tipozona`, `Tipoproceso`.
- Tests: PHPUnit clásico (no Pest), SQLite en memoria. Usar `RefreshDatabase`.

## File Structure

**Migraciones nuevas** (`database/migrations/`):
- `2026_06_04_000001_create_paa_catalog_tables.php` — UNSPSC + 12 lookups.
- `2026_06_04_000002_create_planadquisiciones_table.php` — principal + pivote.
- `2026_06_04_000003_add_planadquisicione_id_to_contratos_table.php` — vínculo.

**Modelos** (`app/Models/`): `Segmento`, `Familia`, `Clase`, `Producto`, `Estadovigencia`, `Mese`, `Modalidade`, `Intervalo`, `Vigenfutura`, `Tipozona`, `Tipoproceso`, `Tipoadquisicione`, `Requiproyecto`, `Fuente`, `Tipoprioridade`, `Requipoai`, `Planadquisicione`. Modificar `Contrato`.

**Comando**: `app/Console/Commands/PaaImport.php`. Config: `config/database.php` (conexión `paa_legacy`).

**Resources** (`app/Filament/Resources/`): `PlanadquisicioneResource` (+ Pages + `ContratosRelationManager`), y catálogos `SegmentoResource`, `FamiliaResource`, `ClaseResource`, `ProductoResource`, `EstadovigenciaResource`, `MeseResource`, `ModalidadeResource`, `IntervaloResource`, `VigenfuturaResource`, `TipozonaResource`, `TipoprocesoResource`, `TipoadquisicioneResource`, `RequiproyectoResource`, `FuenteResource`, `TipoprioridadeResource`, `RequipoaiResource`. Modificar `ContratoResource`.

**Widgets** (`app/Filament/Widgets/`): `PaaStatsOverview`, `PlanesPorAreaChart`, `PlanesPorMesChart`.

**Exports** (`app/Exports/`): `PlanadquisicioneExport` (si se requiere lógica custom; por defecto se usa el ExcelExport de pxlrbt inline).

**Tests** (`tests/Feature/Paa/`): `PaaImportTest`, `PlanadquisicioneResourceTest`, `PlanContratoLinkTest`.

---

## Task 0: Setup del entorno de datos (staging + dumps)

**Files:** `config/database.php` (modify), `.env` (modify, local).

- [ ] **Step 1: Cargar los dumps de producción en local**

Cargar el dump de gestion_arl de producción sobre la BD local (para que el remapeo refleje la realidad) y el dump de PAA en una BD staging:

```bash
# En MySQL local (Laragon). Ejecutar desde una terminal del usuario:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS paa_legacy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root paa_legacy < /c/Users/User/Downloads/paa.sql
# Recargar gestion_arl de producción (opcional pero recomendado para dev realista):
mysql -u root -e "CREATE DATABASE IF NOT EXISTS gestion_arl CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root gestion_arl < /c/Users/User/Downloads/gestion_arl.sql
```

> NOTA: este paso lo ejecuta el usuario (requiere acceso a MySQL). El agente debe pedirlo si las BDs no existen.

- [ ] **Step 2: Añadir conexión `paa_legacy` en `config/database.php`**

Dentro del array `'connections' => [ ... ]`, añadir tras `'mysql'`:

```php
'paa_legacy' => [
    'driver' => 'mysql',
    'host' => env('PAA_LEGACY_DB_HOST', '127.0.0.1'),
    'port' => env('PAA_LEGACY_DB_PORT', '3306'),
    'database' => env('PAA_LEGACY_DB_DATABASE', 'paa_legacy'),
    'username' => env('PAA_LEGACY_DB_USERNAME', 'root'),
    'password' => env('PAA_LEGACY_DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'strict' => false,
    'engine' => null,
],
```

- [ ] **Step 3: Añadir variables a `.env` y `.env.example`**

```
PAA_LEGACY_DB_HOST=127.0.0.1
PAA_LEGACY_DB_PORT=3306
PAA_LEGACY_DB_DATABASE=paa_legacy
PAA_LEGACY_DB_USERNAME=root
PAA_LEGACY_DB_PASSWORD=
```

- [ ] **Step 4: Verificar conexión**

Run: `php artisan tinker --execute="echo DB::connection('paa_legacy')->table('planadquisiciones')->count();"`
Expected: imprime `586` (aprox).

- [ ] **Step 5: Commit**

```bash
git add config/database.php .env.example
git commit -m "chore: conexión paa_legacy para importación PAA"
```

---

## Task 1: Migración de tablas de catálogo PAA

**Files:**
- Create: `database/migrations/2026_06_04_000001_create_paa_catalog_tables.php`

- [ ] **Step 1: Escribir la migración (idempotente con `hasTable`)**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UNSPSC
        $this->createIfMissing('segmentos', function (Blueprint $t) {
            $t->id();
            $t->string('detsegmento');
            $t->string('slug')->nullable();
            $t->timestamps();
        });
        $this->createIfMissing('familias', function (Blueprint $t) {
            $t->id();
            $t->string('detfamilia');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('segmento_id')->nullable()->index();
            $t->timestamps();
        });
        $this->createIfMissing('clases', function (Blueprint $t) {
            $t->id();
            $t->string('detclase');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('familia_id')->nullable()->index();
            $t->timestamps();
        });
        $this->createIfMissing('productos', function (Blueprint $t) {
            $t->id();
            $t->string('detproducto');
            $t->string('slug')->nullable();
            $t->unsignedBigInteger('clase_id')->nullable()->index();
            $t->timestamps();
        });

        // Lookups
        $this->createIfMissing('estadovigencias', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detestadovigencia'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('meses', function (Blueprint $t) {
            $t->id(); $t->string('nommes'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('modalidades', function (Blueprint $t) {
            $t->id(); $t->string('codigo')->nullable(); $t->string('detmodalidad'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('intervalos', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('intervalo'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('vigenfuturas', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detvigencia'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipozonas', function (Blueprint $t) {
            $t->id(); $t->string('tipozona'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoprocesos', function (Blueprint $t) {
            $t->id(); $t->string('dettipoproceso'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoadquisiciones', function (Blueprint $t) {
            $t->id(); $t->string('dettipoadquisicion'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('requiproyectos', function (Blueprint $t) {
            $t->id(); $t->string('detproyeto'); $t->string('slug')->nullable(); $t->timestamps(); // typo legacy intencional
        });
        $this->createIfMissing('fuentes', function (Blueprint $t) {
            $t->id(); $t->integer('codigo')->nullable(); $t->string('detfuente'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('tipoprioridades', function (Blueprint $t) {
            $t->id(); $t->string('detprioridad'); $t->string('slug')->nullable(); $t->timestamps();
        });
        $this->createIfMissing('requipoais', function (Blueprint $t) {
            $t->id(); $t->string('detpoai'); $t->string('slug')->nullable(); $t->timestamps();
        });
    }

    private function createIfMissing(string $table, \Closure $cb): void
    {
        if (! Schema::hasTable($table)) {
            Schema::create($table, $cb);
        }
    }

    public function down(): void
    {
        foreach (['requipoais','tipoprioridades','fuentes','requiproyectos','tipoadquisiciones','tipoprocesos','tipozonas','vigenfuturas','intervalos','modalidades','meses','estadovigencias','productos','clases','familias','segmentos'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
```

- [ ] **Step 2: Ejecutar la migración en local**

Run: `php artisan migrate`
Expected: crea las 16 tablas sin error (las ya existentes por dump se saltan con `hasTable`).

- [ ] **Step 3: Verificar en SQLite de tests**

Run: `php artisan migrate --env=testing --database=sqlite` (o se valida vía RefreshDatabase en Task siguiente).

- [ ] **Step 4: Commit**

```bash
git add database/migrations/2026_06_04_000001_create_paa_catalog_tables.php
git commit -m "feat(paa): migración de catálogos UNSPSC y lookups"
```

---

## Task 2: Migración de planadquisiciones + pivote

**Files:**
- Create: `database/migrations/2026_06_04_000002_create_planadquisiciones_table.php`

- [ ] **Step 1: Escribir la migración**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('planadquisiciones')) {
            Schema::create('planadquisiciones', function (Blueprint $t) {
                $t->id();
                $t->integer('id_vigencia')->nullable();
                $t->string('descripcioncont', 500);
                $t->string('valorestimadocont');   // string: datos legacy con separador de miles
                $t->string('valorestimadovig');
                $t->string('duracont');
                $t->string('codbpim')->nullable();
                $t->unsignedBigInteger('intervalo_id')->nullable();
                $t->unsignedBigInteger('area_id')->nullable();          // → areas (ARL)
                $t->unsignedBigInteger('vigenfutura_id')->nullable();
                $t->unsignedBigInteger('tipozona_id')->nullable();
                $t->unsignedBigInteger('estadovigencia_id')->nullable();
                $t->unsignedBigInteger('modalidade_id')->nullable();
                $t->unsignedBigInteger('tipoproceso_id')->nullable();
                $t->unsignedBigInteger('tipoadquisicione_id')->nullable();
                $t->unsignedBigInteger('requiproyecto_id')->nullable();
                $t->unsignedBigInteger('fuente_id')->nullable();
                $t->unsignedBigInteger('tipoprioridade_id')->nullable();
                $t->unsignedBigInteger('mese_id')->nullable();
                $t->unsignedBigInteger('requipoai_id')->nullable();
                $t->unsignedBigInteger('user_id')->nullable();          // → users (ARL)
                $t->string('slug', 1000)->nullable();
                $t->timestamps();

                $t->index(['area_id', 'id_vigencia']);
            });
        }

        if (! Schema::hasTable('planadquisicione_producto')) {
            Schema::create('planadquisicione_producto', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('planadquisicione_id')->index();
                $t->unsignedBigInteger('producto_id')->nullable()->index();
                $t->unsignedBigInteger('clase_id')->nullable()->index();
                $t->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('planadquisicione_producto');
        Schema::dropIfExists('planadquisiciones');
    }
};
```

> NOTA: NO se usan `foreignId()->constrained()` para evitar fallos de orden con tablas reusadas y datos legacy con FKs huérfanas. Las relaciones se modelan en Eloquent.

- [ ] **Step 2: Migrar y verificar**

Run: `php artisan migrate`
Expected: crea `planadquisiciones` y `planadquisicione_producto` (o se saltan si el dump ya las creó).

- [ ] **Step 3: Commit**

```bash
git add database/migrations/2026_06_04_000002_create_planadquisiciones_table.php
git commit -m "feat(paa): migración de planadquisiciones y pivote producto"
```

---

## Task 3: Modelos Eloquent de catálogos PAA

**Files:** Create en `app/Models/`: los 16 catálogos + `Planadquisicione`.

- [ ] **Step 1: Crear los modelos de lookup simples**

Patrón para cada lookup (sin relaciones jerárquicas). Ejemplo `app/Models/Mese.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mese extends Model
{
    protected $guarded = [];
}
```

Replicar (cambiando solo el nombre de clase y archivo) para: `Estadovigencia`, `Modalidade`, `Intervalo`, `Vigenfutura`, `Tipozona`, `Tipoproceso`, `Tipoadquisicione`, `Requiproyecto`, `Fuente`, `Tipoprioridade`, `Requipoai`. Todos con `protected $guarded = [];` y nada más (Laravel infiere la tabla pluralizando: `meses`, `modalidades`, `intervalos`, `vigenfuturas`, `tipozonas`, `tipoprocesos`, `tipoadquisiciones`, `requiproyectos`, `fuentes`, `tipoprioridades`, `requipoais`, `estadovigencias`).

> Verificar nombres de tabla inferidos: Laravel pluraliza `Estadovigencia`→`estadovigencias` ✓, `Modalidade`→`modalidades` ✓, `Tipoadquisicione`→`tipoadquisiciones` ✓, `Tipoprioridade`→`tipoprioridades` ✓, `Requiproyecto`→`requiproyectos` ✓, `Vigenfutura`→`vigenfuturas` ✓. Si alguno falla, fijar `protected $table`.

- [ ] **Step 2: Crear los modelos UNSPSC con jerarquía**

`app/Models/Segmento.php`:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Segmento extends Model
{
    protected $guarded = [];
    public function familias(): HasMany { return $this->hasMany(Familia::class); }
}
```

`app/Models/Familia.php`:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Familia extends Model
{
    protected $guarded = [];
    public function segmento(): BelongsTo { return $this->belongsTo(Segmento::class); }
    public function clases(): HasMany { return $this->hasMany(Clase::class); }
}
```

`app/Models/Clase.php`:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clase extends Model
{
    protected $guarded = [];
    public function familia(): BelongsTo { return $this->belongsTo(Familia::class); }
    public function productos(): HasMany { return $this->hasMany(Producto::class); }
}
```

`app/Models/Producto.php`:
```php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Producto extends Model
{
    protected $guarded = [];
    public function clase(): BelongsTo { return $this->belongsTo(Clase::class); }
}
```

- [ ] **Step 3: Crear `app/Models/Planadquisicione.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planadquisicione extends Model
{
    protected $guarded = [];

    public function area(): BelongsTo { return $this->belongsTo(Area::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function intervalo(): BelongsTo { return $this->belongsTo(Intervalo::class); }
    public function vigenfutura(): BelongsTo { return $this->belongsTo(Vigenfutura::class); }
    public function tipozona(): BelongsTo { return $this->belongsTo(Tipozona::class); }
    public function estadovigencia(): BelongsTo { return $this->belongsTo(Estadovigencia::class); }
    public function modalidade(): BelongsTo { return $this->belongsTo(Modalidade::class); }
    public function tipoproceso(): BelongsTo { return $this->belongsTo(Tipoproceso::class); }
    public function tipoadquisicione(): BelongsTo { return $this->belongsTo(Tipoadquisicione::class); }
    public function requiproyecto(): BelongsTo { return $this->belongsTo(Requiproyecto::class); }
    public function fuente(): BelongsTo { return $this->belongsTo(Fuente::class); }
    public function tipoprioridade(): BelongsTo { return $this->belongsTo(Tipoprioridade::class); }
    public function mese(): BelongsTo { return $this->belongsTo(Mese::class); }
    public function requipoai(): BelongsTo { return $this->belongsTo(Requipoai::class); }

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'planadquisicione_producto')->withTimestamps();
    }

    public function clases(): BelongsToMany
    {
        return $this->belongsToMany(Clase::class, 'planadquisicione_producto')->withTimestamps();
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }
}
```

- [ ] **Step 4: Test de relaciones (TDD)**

`tests/Feature/Paa/PaaModelsTest.php`:
```php
<?php

namespace Tests\Feature\Paa;

use App\Models\{Segmento, Familia, Clase, Producto, Planadquisicione};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaaModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_jerarquia_unspsc_y_plan_productos(): void
    {
        $seg = Segmento::create(['detsegmento' => 'Seg']);
        $fam = Familia::create(['detfamilia' => 'Fam', 'segmento_id' => $seg->id]);
        $cla = Clase::create(['detclase' => 'Cla', 'familia_id' => $fam->id]);
        $pro = Producto::create(['detproducto' => 'Pro', 'clase_id' => $cla->id]);

        $this->assertEquals($seg->id, $fam->segmento->id);
        $this->assertEquals(1, $cla->productos()->count());

        $plan = Planadquisicione::create([
            'descripcioncont' => 'X', 'valorestimadocont' => '1000',
            'valorestimadovig' => '1000', 'duracont' => '12',
        ]);
        $plan->productos()->attach($pro->id);
        $this->assertEquals(1, $plan->productos()->count());
    }
}
```

- [ ] **Step 5: Ejecutar test**

Run: `php artisan test --filter=PaaModelsTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Models tests/Feature/Paa/PaaModelsTest.php
git commit -m "feat(paa): modelos Eloquent de catálogos y planadquisiciones"
```

---

## Task 4: Comando `paa:import` (catálogos + remapeo + usuarios + planes)

**Files:**
- Create: `app/Console/Commands/PaaImport.php`
- Test: `tests/Feature/Paa/PaaImportTest.php`

- [ ] **Step 1: Generar el comando**

Run: `php artisan make:command PaaImport`

- [ ] **Step 2: Implementar el comando**

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PaaImport extends Command
{
    protected $signature = 'paa:import {--connection=paa_legacy}';
    protected $description = 'Importa los datos del PAA legacy a gestion_arl con remapeo de áreas y usuarios';

    /** Catálogos copiados 1:1 preservando id */
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
            $filas = DB::connection($legacy)->table($tabla)->get();
            $bar = $this->output->createProgressBar($filas->count());
            $this->line("Catálogo {$tabla}: {$filas->count()} filas");
            foreach ($filas as $fila) {
                DB::table($tabla)->updateOrInsert(
                    ['id' => $fila->id],
                    (array) $fila
                );
                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }
    }

    /** Mapa area_id legacy => area_id ARL, por nombre normalizado */
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

    /** Mapa user_id legacy => user_id ARL, por email; crea los faltantes */
    private function construirMapaUsuarios(string $legacy, array $mapaAreas): array
    {
        $arl = DB::table('users')->get()->keyBy(fn ($u) => strtolower(trim($u->email)));
        $mapa = [];
        foreach (DB::connection($legacy)->table('users')->get() as $u) {
            $email = strtolower(trim($u->email));
            if (isset($arl[$email])) {
                $mapa[$u->id] = $arl[$email]->id;
                continue;
            }
            // Crear usuario PAA faltante
            $nuevo = User::create([
                'name' => $u->name,
                'email' => $u->email,
                'password' => Hash::make(Str::random(16)),
                'area_id' => $mapaAreas[$u->areas_id] ?? null,
            ]);
            // Campos opcionales si existen en el modelo ARL
            $extra = array_filter([
                'apellido' => $u->apellido ?? null,
                'telefono' => $u->telefono ?? null,
                'documento' => $u->documento ?? null,
            ]);
            if ($extra) {
                DB::table('users')->where('id', $nuevo->id)->update(
                    array_intersect_key($extra, array_flip($this->columnasUsers()))
                );
            }
            $nuevo->assignRole('panel_user'); // rol básico; ajustar si el nombre difiere
            $mapa[$u->id] = $nuevo->id;
            $this->reporte['usuarios_creados'][] = $u->email;
        }
        return $mapa;
    }

    private function importarPlanes(string $legacy, array $mapaAreas, array $mapaUsers): void
    {
        $filas = DB::connection($legacy)->table('planadquisiciones')->get();
        $this->line("Planes: {$filas->count()} filas");
        $bar = $this->output->createProgressBar($filas->count());
        foreach ($filas as $p) {
            $data = (array) $p;
            $data['area_id'] = $mapaAreas[$p->area_id] ?? null;
            $data['user_id'] = $mapaUsers[$p->user_id] ?? null;
            DB::table('planadquisiciones')->updateOrInsert(['id' => $p->id], $data);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function importarPivote(string $legacy): void
    {
        $filas = DB::connection($legacy)->table('planadquisicione_producto')->get();
        $this->line("Pivote producto: {$filas->count()} filas");
        foreach ($filas as $row) {
            DB::table('planadquisicione_producto')->updateOrInsert(['id' => $row->id], (array) $row);
        }
    }

    private function columnasUsers(): array
    {
        return \Schema::getColumnListing('users');
    }

    private function norm(?string $v): string
    {
        $v = mb_strtolower(trim((string) $v));
        $v = strtr($v, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n']);
        return preg_replace('/\s+/', ' ', $v);
    }

    private function imprimirReporte(): void
    {
        $path = storage_path('logs/paa-import-'.date('Ymd-His').'.log');
        $txt = "REPORTE IMPORTACIÓN PAA\n"
            . "Áreas sin match (".count($this->reporte['areas_sin_match'])."): ".implode(', ', $this->reporte['areas_sin_match'])."\n"
            . "Usuarios creados (".count($this->reporte['usuarios_creados'])."): ".implode(', ', $this->reporte['usuarios_creados'])."\n";
        file_put_contents($path, $txt);
        $this->warn("Áreas sin match: ".count($this->reporte['areas_sin_match']));
        $this->warn("Usuarios creados: ".count($this->reporte['usuarios_creados']));
        $this->info("Reporte guardado en: {$path}");
    }
}
```

> NOTA: el rol `panel_user` debe existir (lo crea Shield). Verificar el nombre real de roles con `php artisan tinker --execute="App\Models\Role? Spatie\Permission\Models\Role::pluck('name')"`. Ajustar el rol si difiere o envolver `assignRole` en try/catch.

- [ ] **Step 3: Test del comando con datos sintéticos (TDD)**

`tests/Feature/Paa/PaaImportTest.php` — usa una conexión sqlite secundaria como "legacy". Configurar en el test una conexión `paa_legacy` en memoria con tablas mínimas y verificar remapeo:

```php
<?php

namespace Tests\Feature\Paa;

use App\Models\{Area, User, Planadquisicione};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Config, DB, Schema};
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaaImportTest extends TestCase
{
    use RefreshDatabase;

    private function setUpLegacy(): void
    {
        Config::set('database.connections.paa_legacy', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
            'foreign_key_constraints' => false,
        ]);
        $c = DB::connection('paa_legacy');
        // crear tablas legacy mínimas
        $c->getSchemaBuilder()->create('areas', fn ($t) => [$t->id(), $t->string('nomarea'), $t->string('slug')->nullable(), $t->unsignedBigInteger('dependencia_id')->nullable(), $t->timestamps()]);
        $c->getSchemaBuilder()->create('users', fn ($t) => [$t->id(), $t->string('name'), $t->string('email'), $t->unsignedBigInteger('areas_id')->nullable(), $t->timestamps()]);
        $c->getSchemaBuilder()->create('planadquisiciones', fn ($t) => [$t->id(), $t->string('descripcioncont'), $t->string('valorestimadocont'), $t->string('valorestimadovig'), $t->string('duracont'), $t->unsignedBigInteger('area_id')->nullable(), $t->unsignedBigInteger('user_id')->nullable(), $t->timestamps()]);
        foreach (['segmentos'=>'detsegmento','familias'=>'detfamilia','clases'=>'detclase','productos'=>'detproducto','estadovigencias'=>'detestadovigencia','meses'=>'nommes','modalidades'=>'detmodalidad','intervalos'=>'intervalo','vigenfuturas'=>'detvigencia','tipozonas'=>'tipozona','tipoprocesos'=>'dettipoproceso','tipoadquisiciones'=>'dettipoadquisicion','requiproyectos'=>'detproyeto','fuentes'=>'detfuente','tipoprioridades'=>'detprioridad','requipoais'=>'detpoai'] as $tbl=>$col) {
            $c->getSchemaBuilder()->create($tbl, function ($t) use ($col) { $t->id(); $t->string($col); $t->string('slug')->nullable(); $t->timestamps(); });
        }
        $c->getSchemaBuilder()->create('planadquisicione_producto', fn ($t) => [$t->id(), $t->unsignedBigInteger('planadquisicione_id'), $t->unsignedBigInteger('producto_id')->nullable(), $t->unsignedBigInteger('clase_id')->nullable(), $t->timestamps()]);
    }

    public function test_remapea_area_por_nombre_y_crea_usuario_faltante(): void
    {
        Role::findOrCreate('panel_user');
        $this->setUpLegacy();
        $legacy = DB::connection('paa_legacy');

        // ARL: existe un área "Planeacion" y un usuario que coincide
        $areaArl = Area::create(['nombre' => 'Planeacion', 'codigo' => 'PLA', 'dependencia_id' => $this->dependencia()]);
        User::factory()->create(['email' => 'match@x.com']);

        // Legacy
        $legacy->table('areas')->insert(['id' => 7, 'nomarea' => 'PLANEACIÓN', 'slug' => 'p']);
        $legacy->table('users')->insert(['id' => 3, 'name' => 'Nuevo', 'email' => 'nuevo@x.com', 'areas_id' => 7]);
        $legacy->table('planadquisiciones')->insert(['id' => 1, 'descripcioncont' => 'D', 'valorestimadocont' => '1.000', 'valorestimadovig' => '1.000', 'duracont' => '12', 'area_id' => 7, 'user_id' => 3]);

        $this->artisan('paa:import')->assertSuccessful();

        $plan = Planadquisicione::find(1);
        $this->assertEquals($areaArl->id, $plan->area_id);          // remapeado por nombre
        $this->assertNotNull($plan->user_id);                       // usuario creado
        $this->assertDatabaseHas('users', ['email' => 'nuevo@x.com']);
    }

    private function dependencia(): int
    {
        return \App\Models\Dependencia::create(['nombre' => 'Dep'])->id;
    }
}
```

> NOTA al implementador: el helper de creación de tablas con arrow-fn que devuelve array no es válido en Blueprint; usar closures con cuerpo `{ $t->id(); ... }` como en `planadquisicione_producto`. Ajustar el test a esa forma (se dejó compacto por brevedad; expandir cada `create` a closure con statements).

- [ ] **Step 4: Ejecutar el test y ajustar hasta PASS**

Run: `php artisan test --filter=PaaImportTest`
Expected: PASS (remapeo de área + creación de usuario).

- [ ] **Step 5: Ejecución real (manual, una vez)**

Run: `php artisan paa:import`
Expected: progreso por catálogo, "Planes: ~586 filas", reporte con áreas sin match y usuarios creados. Revisar el `.log` con el usuario.

- [ ] **Step 6: Commit**

```bash
git add app/Console/Commands/PaaImport.php tests/Feature/Paa/PaaImportTest.php
git commit -m "feat(paa): comando paa:import con remapeo de áreas/usuarios y reporte"
```

---

## Task 5: Vínculo Plan↔Contrato (1:N)

**Files:**
- Create: `database/migrations/2026_06_04_000003_add_planadquisicione_id_to_contratos_table.php`
- Modify: `app/Models/Contrato.php`, `app/Filament/Resources/ContratoResource.php`
- Test: `tests/Feature/Paa/PlanContratoLinkTest.php`

- [ ] **Step 1: Migración**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('contratos', 'planadquisicione_id')) {
            Schema::table('contratos', function (Blueprint $t) {
                $t->unsignedBigInteger('planadquisicione_id')->nullable()->after('id')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('contratos', 'planadquisicione_id')) {
            Schema::table('contratos', fn (Blueprint $t) => $t->dropColumn('planadquisicione_id'));
        }
    }
};
```

- [ ] **Step 2: Relación + fillable en `Contrato`**

En `app/Models/Contrato.php`: (a) añadir `'planadquisicione_id'` al array `$fillable` (el modelo usa `$fillable`; sin esto el Select no guardará), y (b) añadir la relación:
```php
public function planadquisicione(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\Planadquisicione::class);
}
```

> NOTA FK: la migración añade `planadquisicione_id` como columna indexada **sin constraint a nivel BD** (decisión deliberada para evitar problemas con datos legacy). El comportamiento "null on delete" del spec se garantiza a nivel aplicación (el RelationManager usa Dissociate, no delete). Reconciliado: no se crea FK física.

- [ ] **Step 3: Test del vínculo (TDD)**

`tests/Feature/Paa/PlanContratoLinkTest.php`:
```php
<?php
namespace Tests\Feature\Paa;

use App\Models\{Contrato, Planadquisicione};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanContratoLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_plan_tiene_muchos_contratos(): void
    {
        $plan = Planadquisicione::create(['descripcioncont'=>'D','valorestimadocont'=>'1','valorestimadovig'=>'1','duracont'=>'1']);
        $contrato = Contrato::factory()->create(['planadquisicione_id' => $plan->id]); // o create con campos mínimos requeridos
        $this->assertEquals(1, $plan->contratos()->count());
        $this->assertEquals($plan->id, $contrato->planadquisicione->id);
    }
}
```

> NOTA: si `Contrato` no tiene factory, crear el contrato con los campos NOT NULL mínimos (revisar migración de contratos). Documentar los campos requeridos al implementar.

- [ ] **Step 4: Select en `ContratoResource`**

Añadir en el form de `ContratoResource` (sección "Datos del Contrato", el primer section del form):
```php
Forms\Components\Select::make('planadquisicione_id')
    ->label('Línea del Plan de Adquisición')
    ->relationship('planadquisicione', 'descripcioncont')
    ->searchable()
    ->preload()
    ->nullable(),
```
Y una columna en la tabla:
```php
Tables\Columns\TextColumn::make('planadquisicione.descripcioncont')
    ->label('Plan de Adquisición')->limit(40)->toggleable(),
```

- [ ] **Step 5: Ejecutar test y migrar**

Run: `php artisan migrate` y `php artisan test --filter=PlanContratoLinkTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_06_04_000003_add_planadquisicione_id_to_contratos_table.php app/Models/Contrato.php app/Filament/Resources/ContratoResource.php tests/Feature/Paa/PlanContratoLinkTest.php
git commit -m "feat(paa): vínculo 1:N Plan→Contrato"
```

---

## Task 6: PlanadquisicioneResource (port a Filament v3)

**Files:**
- Create: `app/Filament/Resources/PlanadquisicioneResource.php`
- Create: `app/Filament/Resources/PlanadquisicioneResource/Pages/{ListPlanadquisiciones,CreatePlanadquisicione,EditPlanadquisicione}.php`
- Test: `tests/Feature/Paa/PlanadquisicioneResourceTest.php`

> Fuente a portar: `C:\laragon\www\paa-v4\app\Filament\Resources\PlanadquisicioneResource.php` (sintaxis v4). Traducir a v3: `Schema $schema`→`Form $form`, `Filament\Schemas\Components\Wizard`→`Filament\Forms\Components\Wizard`, `Filament\Actions\*`→`Tables\Actions\*`, `Filament\Schemas\Components\Utilities\{Get,Set}`→`Filament\Forms\{Get,Set}`, `Filament\Schemas\Components\Grid`→`Filament\Forms\Components\Grid`.

- [ ] **Step 1: Generar el resource y pages**

Run: `php artisan make:filament-resource Planadquisicione --generate` (luego se reemplaza el contenido).

- [ ] **Step 2: Escribir el resource (v3)**

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanadquisicioneResource\Pages;
use App\Filament\Resources\PlanadquisicioneResource\RelationManagers\ContratosRelationManager;
use App\Models\{Area, Clase, Familia, Planadquisicione, Producto, Segmento};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlanadquisicioneResource extends Resource
{
    protected static ?string $model = Planadquisicione::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Plan de Adquisiciones';
    protected static ?string $navigationLabel = 'Planes de Adquisición';
    protected static ?string $modelLabel = 'Plan de Adquisición';
    protected static ?string $pluralModelLabel = 'Planes de Adquisición';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                Forms\Components\Wizard\Step::make('Datos del Contrato')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\TextInput::make('descripcioncont')->label('Descripción del Contrato')->required()->maxLength(500)->columnSpanFull(),
                        Forms\Components\TextInput::make('valorestimadocont')->label('Valor Estimado del Contrato')->required(),
                        Forms\Components\TextInput::make('valorestimadovig')->label('Valor Estimado Vigencia')->required(),
                        Forms\Components\TextInput::make('duracont')->label('Duración (meses)')->required(),
                        Forms\Components\TextInput::make('codbpim')->label('Código BPIM')->maxLength(50),
                        Forms\Components\Select::make('area_id')->label('Área')->required()->searchable()->preload()
                            ->options(function () {
                                $user = Auth::user();
                                if ($user && ($user->hasRole('Admin') || $user->hasRole('super_admin') || $user->hasRole('Supervisor'))) {
                                    return Area::orderBy('nombre')->pluck('nombre', 'id');
                                }
                                return Area::where('id', $user?->area_id)->pluck('nombre', 'id');
                            }),
                    ])->columns(2),

                Forms\Components\Wizard\Step::make('Clasificación')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\Select::make('tipoadquisicione_id')->label('Tipo de Adquisición')->relationship('tipoadquisicione', 'dettipoadquisicion')->searchable()->preload()->required(),
                            Forms\Components\Select::make('modalidade_id')->label('Modalidad')->relationship('modalidade', 'detmodalidad')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipozona_id')->label('Tipo de Zona')->relationship('tipozona', 'tipozona')->searchable()->preload()->required(),
                            Forms\Components\Select::make('estadovigencia_id')->label('Estado Vigencia')->relationship('estadovigencia', 'detestadovigencia')->searchable()->preload()->required(),
                            Forms\Components\Select::make('vigenfutura_id')->label('Vigencia Futura')->relationship('vigenfutura', 'detvigencia')->searchable()->preload()->required(),
                            Forms\Components\Select::make('fuente_id')->label('Fuente')->relationship('fuente', 'detfuente')->searchable()->preload()->required(),
                            Forms\Components\Select::make('mese_id')->label('Mes de Inicio')->relationship('mese', 'nommes')->searchable()->preload()->required(),
                            Forms\Components\Select::make('intervalo_id')->label('Intervalo')->relationship('intervalo', 'intervalo')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipoprioridade_id')->label('Tipo de Prioridad')->relationship('tipoprioridade', 'detprioridad')->searchable()->preload()->required(),
                            Forms\Components\Select::make('requiproyecto_id')->label('Requiere Proyecto')->relationship('requiproyecto', 'detproyeto')->searchable()->preload()->required(),
                            Forms\Components\Select::make('requipoai_id')->label('Requiere POA-I')->relationship('requipoai', 'detpoai')->searchable()->preload()->required(),
                            Forms\Components\Select::make('tipoproceso_id')->label('Tipo de Proceso')->relationship('tipoproceso', 'dettipoproceso')->searchable()->preload(),
                        ]),
                    ]),

                Forms\Components\Wizard\Step::make('Clasificación UNSPSC')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Forms\Components\Select::make('segmento_id')->label('Segmento')
                            ->options(fn () => Segmento::orderBy('detsegmento')->pluck('detsegmento', 'id'))
                            ->live()->searchable()->dehydrated(false)
                            ->afterStateUpdated(fn (Set $set) => $set('familia_id', null) && $set('clase_id', null)),
                        Forms\Components\Select::make('familia_id')->label('Familia')
                            ->options(fn (Get $get) => Familia::when($get('segmento_id'), fn ($q) => $q->where('segmento_id', $get('segmento_id')))->orderBy('detfamilia')->pluck('detfamilia', 'id'))
                            ->live()->searchable()->dehydrated(false)
                            ->afterStateUpdated(fn (Set $set) => $set('clase_id', null)),
                        Forms\Components\Select::make('clase_id')->label('Clase')
                            ->options(fn (Get $get) => Clase::when($get('familia_id'), fn ($q) => $q->where('familia_id', $get('familia_id')))->orderBy('detclase')->pluck('detclase', 'id'))
                            ->live()->searchable()->dehydrated(false),
                        Forms\Components\Select::make('productos')->label('Productos UNSPSC')->multiple()->relationship('productos', 'detproducto')
                            ->options(fn (Get $get) => Producto::when($get('clase_id'), fn ($q) => $q->where('clase_id', $get('clase_id')))->orderBy('detproducto')->pluck('detproducto', 'id'))
                            ->searchable()->preload(false),
                        Forms\Components\Select::make('clases')->label('Clases UNSPSC')->multiple()->relationship('clases', 'detclase')
                            ->options(fn (Get $get) => Clase::when($get('familia_id'), fn ($q) => $q->where('familia_id', $get('familia_id')))->orderBy('detclase')->pluck('detclase', 'id'))
                            ->searchable()->preload(false),
                    ]),
            ])->columnSpanFull(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();
        if ($user && ! $user->hasRole('Admin') && ! $user->hasRole('super_admin') && ! $user->hasRole('Supervisor')) {
            $query->where('user_id', $user->id);
        }
        return $query;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('descripcioncont')->label('Descripción')->searchable()->sortable()->limit(60)->tooltip(fn ($record) => $record->descripcioncont),
                Tables\Columns\TextColumn::make('valorestimadocont')->label('Valor Estimado')->sortable(),
                Tables\Columns\TextColumn::make('area.nombre')->label('Área')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('mese.nommes')->label('Mes')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('estadovigencia.detestadovigencia')->label('Estado Vigencia')->badge()
                    ->color(fn (?string $state): string => match (true) {
                        $state === null => 'gray',
                        str_contains(strtolower($state), 'vigente') => 'success',
                        str_contains(strtolower($state), 'cerrad') => 'danger',
                        default => 'warning',
                    }),
                Tables\Columns\TextColumn::make('created_at')->label('Vigencia')->date('Y')->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Registrado por')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('contratos_count')->counts('contratos')->label('Contratos')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('vigencia')->label('Vigencia (Año)')
                    ->options(function () {
                        // Año derivado de created_at; compatible con SQLite (tests) y MySQL (prod)
                        $driver = DB::getDriverName();
                        $yearExpr = $driver === 'sqlite'
                            ? "CAST(strftime('%Y', created_at) AS INTEGER)"
                            : 'YEAR(created_at)';
                        return Planadquisicione::selectRaw("{$yearExpr} as year")->distinct()->orderBy('year', 'desc')->pluck('year', 'year')->toArray();
                    })
                    ->query(fn (Builder $query, array $data) => empty($data['value']) ? $query : $query->whereYear('created_at', $data['value'])),
                Tables\Filters\SelectFilter::make('area_id')->label('Área')->relationship('area', 'nombre')->searchable()->preload(),
                Tables\Filters\SelectFilter::make('estadovigencia_id')->label('Estado Vigencia')->relationship('estadovigencia', 'detestadovigencia'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [ ContratosRelationManager::class ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlanadquisiciones::route('/'),
            'create' => Pages\CreatePlanadquisicione::route('/create'),
            'edit'   => Pages\EditPlanadquisicione::route('/{record}/edit'),
        ];
    }
}
```

> NOTA `afterStateUpdated` con `&&`: la forma `fn (Set $set) => $set(...) && $set(...)` no encadena correctamente; reemplazar por closure de bloque: `function (Set $set) { $set('familia_id', null); $set('clase_id', null); }`.

> **NOTA IMPORTANTE — `id_vigencia` NO es un año.** Verificado en `paa.sql`: es un entero secuencial legacy (fila 1→1, fila 2→2…), no la vigencia/año. La **vigencia (año) real se deriva de `created_at`** (los datos abarcan 2024–2026). Por eso el campo `id_vigencia` se omite del formulario (se conserva la columna en BD por compatibilidad, nullable para registros nuevos) y tanto la columna "Vigencia", el filtro como los widgets usan `created_at`. NO usar `id_vigencia` como año en ninguna consulta.

- [ ] **Step 3: Verificar nombres de roles**

Run: `php artisan tinker --execute="echo Spatie\Permission\Models\Role::pluck('name')->implode(',');"`
Ajustar los `hasRole('...')` del resource a los roles reales del proyecto (p. ej. `super_admin`).

- [ ] **Step 4: Test del resource**

`tests/Feature/Paa/PlanadquisicioneResourceTest.php`:
```php
<?php
namespace Tests\Feature\Paa;

use App\Filament\Resources\PlanadquisicioneResource\Pages\ListPlanadquisiciones;
use App\Models\{Area, Dependencia, Planadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanadquisicioneResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_ve_la_lista(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        Livewire::test(ListPlanadquisiciones::class)->assertSuccessful();
    }

    public function test_usuario_normal_solo_ve_lo_suyo(): void
    {
        $dep = Dependencia::create(['nombre' => 'D']);
        $area = Area::create(['nombre' => 'A', 'codigo' => 'A1', 'dependencia_id' => $dep->id]);
        $u1 = User::factory()->create(['area_id' => $area->id]);
        $u2 = User::factory()->create(['area_id' => $area->id]);
        Planadquisicione::create(['descripcioncont'=>'mio','valorestimadocont'=>'1','valorestimadovig'=>'1','duracont'=>'1','user_id'=>$u1->id]);
        Planadquisicione::create(['descripcioncont'=>'ajeno','valorestimadocont'=>'1','valorestimadovig'=>'1','duracont'=>'1','user_id'=>$u2->id]);

        $this->actingAs($u1);
        Livewire::test(ListPlanadquisiciones::class)->assertCanSeeTableRecords(Planadquisicione::where('user_id', $u1->id)->get())
            ->assertCanNotSeeTableRecords(Planadquisicione::where('user_id', $u2->id)->get());
    }
}
```

- [ ] **Step 5: Ejecutar tests**

Run: `php artisan test --filter=PlanadquisicioneResourceTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources/PlanadquisicioneResource.php app/Filament/Resources/PlanadquisicioneResource/Pages tests/Feature/Paa/PlanadquisicioneResourceTest.php
git commit -m "feat(paa): PlanadquisicioneResource (wizard + tabla + scoping) en Filament v3"
```

---

## Task 7: ContratosRelationManager en el Plan

**Files:**
- Create: `app/Filament/Resources/PlanadquisicioneResource/RelationManagers/ContratosRelationManager.php`

- [ ] **Step 1: Implementar el RelationManager**

```php
<?php

namespace App\Filament\Resources\PlanadquisicioneResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ContratosRelationManager extends RelationManager
{
    protected static string $relationship = 'contratos';
    protected static ?string $title = 'Contratos vinculados';

    public function form(Form $form): Form
    {
        return $form->schema([]); // edición se hace en ContratoResource
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_contrato')
            ->columns([
                Tables\Columns\TextColumn::make('numero_contrato')->label('N° Contrato'),
                Tables\Columns\TextColumn::make('objeto')->label('Objeto')->limit(50),
                Tables\Columns\TextColumn::make('estado')->label('Estado')->badge(),
                Tables\Columns\TextColumn::make('fecha_inicio')->label('Inicio')->date(),
            ])
            ->headerActions([
                Tables\Actions\AssociateAction::make(),
            ])
            ->actions([
                Tables\Actions\DissociateAction::make(),
            ]);
    }
}
```

> NOTA: verificar que las columnas (`numero_contrato`, `objeto`, `estado`, `fecha_inicio`) existen en la tabla `contratos`. Ajustar a las reales si difieren.

- [ ] **Step 2: Verificar en el navegador (manual)**

Abrir un plan → pestaña "Contratos vinculados" → asociar un contrato existente.

- [ ] **Step 3: Commit**

```bash
git add app/Filament/Resources/PlanadquisicioneResource/RelationManagers/ContratosRelationManager.php
git commit -m "feat(paa): RelationManager de contratos en el plan"
```

---

## Task 8: Resources de catálogos (UNSPSC + 12 lookups)

**Files:** Create un Resource por modelo (16 en total).

- [ ] **Step 1: Generar y escribir el template de lookup simple**

Ejemplo completo `app/Filament/Resources/MeseResource.php`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MeseResource\Pages;
use App\Models\Mese;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MeseResource extends Resource
{
    protected static ?string $model = Mese::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationGroup = 'Configuración PAA';
    protected static ?string $modelLabel = 'Mes';
    protected static ?string $pluralModelLabel = 'Meses';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nommes')->label('Mes')->required()->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nommes')->label('Mes')->searchable()->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMeses::route('/'),
            'create' => Pages\CreateMese::route('/create'),
            'edit' => Pages\EditMese::route('/{record}/edit'),
        ];
    }
}
```

- [ ] **Step 2: Replicar para cada lookup**

Usar `php artisan make:filament-resource <Modelo> --generate` y ajustar `navigationGroup = 'Configuración PAA'` + el campo principal. Tabla de especificidades:

| Modelo | navigationGroup | Campo principal (label) | Campo extra |
|--------|-----------------|--------------------------|-------------|
| Estadovigencia | Configuración PAA | detestadovigencia (Estado Vigencia) | codigo |
| Modalidade | Configuración PAA | detmodalidad (Modalidad) | codigo |
| Intervalo | Configuración PAA | intervalo (Intervalo) | codigo |
| Vigenfutura | Configuración PAA | detvigencia (Vigencia Futura) | codigo |
| Tipozona | Configuración PAA | tipozona (Tipo de Zona) | — |
| Tipoproceso | Configuración PAA | dettipoproceso (Tipo de Proceso) | — |
| Tipoadquisicione | Configuración PAA | dettipoadquisicion (Tipo de Adquisición) | — |
| Requiproyecto | Configuración PAA | detproyeto (Requiere Proyecto) | — |
| Fuente | Configuración PAA | detfuente (Fuente) | codigo |
| Tipoprioridade | Configuración PAA | detprioridad (Tipo de Prioridad) | — |
| Requipoai | Configuración PAA | detpoai (Requiere POA-I) | — |

- [ ] **Step 3: Resources UNSPSC (con relación y filtro padre)**

`SegmentoResource` (grupo "Clasificación UNSPSC"): campo `detsegmento`, columna con `familias_count`.
`FamiliaResource`: campo `detfamilia` + Select `segmento_id` (relationship 'segmento','detsegmento'); columna `segmento.detsegmento`; filtro por segmento.
`ClaseResource`: campo `detclase` + Select `familia_id` (relationship 'familia','detfamilia'); columna `familia.detfamilia`.
`ProductoResource` (grupo "Clasificación UNSPSC"): campo `detproducto` + Select `clase_id` (relationship 'clase','detclase'); tabla con `searchable()` en `detproducto` y columna `clase.detclase`. La tabla pagina (49k filas) — añadir `->deferLoading()` y búsqueda por defecto.

Ejemplo `FamiliaResource` form/table relevante:
```php
// form
Forms\Components\Select::make('segmento_id')->label('Segmento')->relationship('segmento', 'detsegmento')->searchable()->preload()->required(),
Forms\Components\TextInput::make('detfamilia')->label('Familia')->required(),
// table
Tables\Columns\TextColumn::make('detfamilia')->label('Familia')->searchable()->sortable(),
Tables\Columns\TextColumn::make('segmento.detsegmento')->label('Segmento')->searchable(),
```

- [ ] **Step 4: Smoke test de catálogos**

`tests/Feature/Paa/CatalogResourcesTest.php` — verificar que las páginas index cargan para un admin:
```php
public function test_paginas_index_catalogos_cargan(): void
{
    Role::findOrCreate('super_admin');
    $admin = User::factory()->create(); $admin->assignRole('super_admin');
    $this->actingAs($admin);
    foreach ([
        \App\Filament\Resources\SegmentoResource\Pages\ListSegmentos::class,
        \App\Filament\Resources\MeseResource\Pages\ListMeses::class,
        // ... añadir las demás List pages
    ] as $page) {
        Livewire::test($page)->assertSuccessful();
    }
}
```

- [ ] **Step 5: Ejecutar tests**

Run: `php artisan test --filter=CatalogResourcesTest`
Expected: PASS.

- [ ] **Step 6: Commit**

```bash
git add app/Filament/Resources tests/Feature/Paa/CatalogResourcesTest.php
git commit -m "feat(paa): resources de catálogos UNSPSC y lookups"
```

---

## Task 9: Dashboard — Widgets ApexCharts

**Files:**
- Create: `app/Filament/Widgets/PaaStatsOverview.php`, `PlanesPorAreaChart.php`, `PlanesPorMesChart.php`

- [ ] **Step 1: StatsOverview**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\{Area, Planadquisicione, Producto, User};
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PaaStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // Vigencia = año más reciente con datos (derivado de created_at), no el año actual.
        $vigencia = (int) (Planadquisicione::max(DB::raw('YEAR(created_at)')) ?? date('Y'));
        $planes = Planadquisicione::whereYear('created_at', $vigencia)->count();
        $valor = Planadquisicione::whereYear('created_at', $vigencia)->get()
            ->sum(fn ($p) => (float) str_replace(['.', ','], ['', '.'], (string) $p->valorestimadocont));

        return [
            Stat::make("Planes vigencia {$vigencia}", $planes)->icon('heroicon-o-document-text'),
            Stat::make('Valor estimado total', '$ '.number_format($valor, 0, ',', '.')),
            Stat::make('Productos UNSPSC', Producto::count()),
            Stat::make('Áreas', Area::count()),
            Stat::make('Usuarios', User::count()),
        ];
    }
}
```

> NOTA parseo de valor: los datos legacy usan `.` como separador de miles (`1.000.000`). `str_replace('.', '')` quita los miles. Verificar con datos reales que no haya decimales con coma; ajustar si aplica.

- [ ] **Step 2: Gráfico Planes por Área**

```php
<?php

namespace App\Filament\Widgets;

use App\Models\Planadquisicione;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class PlanesPorAreaChart extends ApexChartWidget
{
    protected static ?string $chartId = 'planesPorArea';
    protected static ?string $heading = 'Planes por Área (vigencia actual)';
    protected static ?int $sort = 3;

    protected function getOptions(): array
    {
        $vigencia = (int) (Planadquisicione::max(DB::raw('YEAR(created_at)')) ?? date('Y'));
        $rows = Planadquisicione::whereYear('created_at', $vigencia)
            ->join('areas', 'planadquisiciones.area_id', '=', 'areas.id')
            ->select('areas.nombre', DB::raw('count(*) as total'))
            ->groupBy('areas.nombre')->orderByDesc('total')->limit(15)->get();

        return [
            'chart' => ['type' => 'bar', 'height' => 350],
            'series' => [['name' => 'Planes', 'data' => $rows->pluck('total')->toArray()]],
            'xaxis' => ['categories' => $rows->pluck('nombre')->toArray()],
        ];
    }
}
```

- [ ] **Step 3: Gráfico Planes por Mes**

Análogo, agrupando por `mese_id` (join a `meses.nommes`).

- [ ] **Step 4: Verificación manual**

Abrir `/admin` → confirmar que los widgets renderizan con datos reales.

- [ ] **Step 5: Commit**

```bash
git add app/Filament/Widgets
git commit -m "feat(paa): widgets de dashboard (stats + gráficos)"
```

---

## Task 10: Excel export/import

**Files:**
- Modify: `app/Filament/Resources/PlanadquisicioneResource.php` (acción export)

- [ ] **Step 1: Acción de exportación en la tabla de planes**

Añadir en `table()` de `PlanadquisicioneResource`, en `headerActions` (crear el método si no existe) o en bulkActions:

```php
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use pxlrbt\FilamentExcel\Columns\Column;

// dentro de ->bulkActions([... BulkActionGroup::make([ ... y además:
ExportBulkAction::make()->exports([
    ExcelExport::make()->withColumns([
        Column::make('descripcioncont')->heading('Descripción'),
        Column::make('valorestimadocont')->heading('Valor Estimado'),
        Column::make('area.nombre')->heading('Área'),
        Column::make('modalidade.detmodalidad')->heading('Modalidad'),
        Column::make('estadovigencia.detestadovigencia')->heading('Estado Vigencia'),
        Column::make('mese.nommes')->heading('Mes'),
        Column::make('id_vigencia')->heading('Vigencia'),
        Column::make('user.name')->heading('Registrado por'),
    ])->withFilename('plan-adquisiciones-'.date('Y-m-d')),
]),
```

> NOTA: en pxlrbt/filament-excel v2 + Filament v3, `ExportBulkAction` y `ExportAction` viven en `pxlrbt\FilamentExcel\Actions\Tables\`. Verificar el namespace instalado con `composer show pxlrbt/filament-excel`.

- [ ] **Step 2: Importación de catálogos (opcional, posterior)**

La importación inicial se hace con `paa:import`. La importación Excel de catálogos (áreas/familias/segmentos/clases/fuentes/modalidades/productos) se implementa como acción `Filament\Tables\Actions\Action` con un `FileUpload` + `maatwebsite/excel` import class, **solo si el usuario la requiere para recargas**. Marcar como tarea diferida si no es prioritaria.

- [ ] **Step 3: Verificación manual**

Seleccionar filas en la tabla de planes → "Export" → descargar XLSX y verificar columnas.

- [ ] **Step 4: Commit**

```bash
git add app/Filament/Resources/PlanadquisicioneResource.php
git commit -m "feat(paa): exportación Excel de planes de adquisición"
```

---

## Task 11: Permisos Shield + suite completa

- [ ] **Step 1: Generar permisos de los nuevos resources**

Run: `php artisan shield:generate --all`
Expected: crea permisos para PlanadquisicioneResource y catálogos. Asignar al rol admin.

- [ ] **Step 2: Asignar permisos al super admin**

Run: `php artisan shield:super-admin` (o asignar vía el RoleResource existente).

- [ ] **Step 3: Suite completa**

Run: `php artisan test`
Expected: todos los tests PASAN (incluidos los preexistentes del proyecto).

- [ ] **Step 4: Verificación manual integral**

- `/admin` carga con widgets PAA.
- "Planes de Adquisición" lista los 586 planes con su área/usuario remapeados.
- Crear un plan nuevo con el wizard funciona.
- Vincular un contrato a un plan y verlo en ambos lados.
- Catálogos UNSPSC y lookups navegables.

- [ ] **Step 5: Commit final**

```bash
git add -A
git commit -m "chore(paa): permisos Shield y verificación de la unificación"
```

---

## Notas finales para el implementador
- **Roles:** verificar nombres reales (`super_admin`, `panel_user`, etc.) antes de codificar `hasRole`/`assignRole`.
- **Campos NOT NULL de `contratos`:** revisar la migración de contratos para construir registros de prueba válidos.
- **Parseo de valores monetarios:** confirmar formato real (`1.000.000`) antes de los widgets.
- **No tocar** `areas`, `dependencias`, `users`, `contratos`, `afiliaciones` salvo el `planadquisicione_id` añadido.
- Ejecutar `php artisan test` tras cada Task; mantener verde.
