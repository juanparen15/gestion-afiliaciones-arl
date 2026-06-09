<?php

namespace Tests\Feature\Paa;

use App\Models\{Area, Dependencia, Planadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Config, DB};
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
        // Purge any cached connection so Config takes effect
        DB::purge('paa_legacy');

        $sb = DB::connection('paa_legacy')->getSchemaBuilder();
        $sb->create('areas', function ($t) {
            $t->id(); $t->string('nomarea'); $t->string('slug')->nullable();
            $t->unsignedBigInteger('dependencia_id')->nullable(); $t->timestamps();
        });
        $sb->create('users', function ($t) {
            $t->id(); $t->string('name'); $t->string('email');
            $t->unsignedBigInteger('areas_id')->nullable(); $t->timestamps();
        });
        $sb->create('planadquisiciones', function ($t) {
            $t->id(); $t->string('descripcioncont'); $t->string('valorestimadocont');
            $t->string('valorestimadovig'); $t->string('duracont');
            $t->unsignedBigInteger('area_id')->nullable(); $t->unsignedBigInteger('user_id')->nullable();
            $t->timestamps();
        });
        $cols = [
            'segmentos' => 'detsegmento',
            'familias' => 'detfamilia',
            'clases' => 'detclase',
            'productos' => 'detproducto',
            'estadovigencias' => 'detestadovigencia',
            'meses' => 'nommes',
            'modalidades' => 'detmodalidad',
            'intervalos' => 'intervalo',
            'vigenfuturas' => 'detvigencia',
            'tipozonas' => 'tipozona',
            'tipoprocesos' => 'dettipoproceso',
            'tipoadquisiciones' => 'dettipoadquisicion',
            'requiproyectos' => 'detproyeto',
            'fuentes' => 'detfuente',
            'tipoprioridades' => 'detprioridad',
            'requipoais' => 'detpoai',
        ];
        foreach ($cols as $tbl => $col) {
            $sb->create($tbl, function ($t) use ($col) {
                $t->id(); $t->string($col); $t->string('slug')->nullable(); $t->timestamps();
            });
        }
        $sb->create('planadquisicione_producto', function ($t) {
            $t->id(); $t->unsignedBigInteger('planadquisicione_id');
            $t->unsignedBigInteger('producto_id')->nullable(); $t->unsignedBigInteger('clase_id')->nullable();
            $t->timestamps();
        });
    }

    public function test_remapea_area_por_nombre_y_crea_usuario_faltante(): void
    {
        // Ensure the basic role exists (matches production role name)
        Role::findOrCreate('Dependencia');

        $this->setUpLegacy();
        $legacy = DB::connection('paa_legacy');

        // Set up target-side data: dependencia + area with required NOT NULL columns
        $dep = Dependencia::create(['nombre' => 'Dep Principal']);
        $areaArl = Area::create([
            'nombre' => 'Planeacion',
            'codigo' => 'PLA',
            'dependencia_id' => $dep->id,
        ]);
        User::factory()->create(['email' => 'match@x.com']);

        // Set up legacy-side data: area name has accent (PLANEACIÓN → normalises to 'planeacion')
        $legacy->table('areas')->insert(['id' => 7, 'nomarea' => 'PLANEACIÓN', 'slug' => 'p']);
        $legacy->table('users')->insert(['id' => 3, 'name' => 'Nuevo', 'email' => 'nuevo@x.com', 'areas_id' => 7]);
        $legacy->table('planadquisiciones')->insert([
            'id' => 1,
            'descripcioncont' => 'D',
            'valorestimadocont' => '1.000',
            'valorestimadovig' => '1.000',
            'duracont' => '12',
            'area_id' => 7,
            'user_id' => 3,
        ]);

        $this->artisan('paa:import')->assertSuccessful();

        $plan = Planadquisicione::find(1);
        $this->assertNotNull($plan, 'El plan debe haberse importado');
        $this->assertEquals($areaArl->id, $plan->area_id, 'area_id debe remapearse por nombre normalizado');
        $this->assertNotNull($plan->user_id, 'user_id debe asignarse al usuario creado');
        $this->assertDatabaseHas('users', ['email' => 'nuevo@x.com']);
    }
}
