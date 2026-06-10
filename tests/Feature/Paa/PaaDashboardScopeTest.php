<?php

namespace Tests\Feature\Paa;

use App\Models\{Area, Dependencia, Planadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaaDashboardScopeTest extends TestCase
{
    use RefreshDatabase;

    private Dependencia $depA;
    private Dependencia $depB;
    private Area $areaA1;
    private Area $areaB1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->depA = Dependencia::create(['nombre' => 'Dependencia A']);
        $this->depB = Dependencia::create(['nombre' => 'Dependencia B']);
        $this->areaA1 = Area::create(['nombre' => 'Área A1', 'codigo' => 'A1', 'dependencia_id' => $this->depA->id]);
        $this->areaB1 = Area::create(['nombre' => 'Área B1', 'codigo' => 'B1', 'dependencia_id' => $this->depB->id]);

        $base = ['valorestimadocont' => '1.000.000', 'valorestimadovig' => '1.000.000', 'duracont' => '6'];
        // Plan del área A1 (pertenece a Dep A)
        Planadquisicione::create($base + ['descripcioncont' => 'A1', 'area_id' => $this->areaA1->id, 'dependencia_id' => $this->depA->id]);
        // Plan directo de Dep A (sin área)
        Planadquisicione::create($base + ['descripcioncont' => 'A-dir', 'dependencia_id' => $this->depA->id]);
        // Plan del área B1 (pertenece a Dep B)
        Planadquisicione::create($base + ['descripcioncont' => 'B1', 'area_id' => $this->areaB1->id, 'dependencia_id' => $this->depB->id]);
    }

    private function user(array $attrs = [], ?string $role = null): User
    {
        $u = User::factory()->create($attrs);
        if ($role) {
            Role::findOrCreate($role);
            $u->assignRole($role);
        }

        return $u;
    }

    public function test_super_admin_ve_todos(): void
    {
        $u = $this->user([], 'super_admin');
        $this->assertSame(3, Planadquisicione::visibleTo($u)->count());
    }

    public function test_ssst_ve_todos(): void
    {
        $u = $this->user([], 'SSST');
        $this->assertSame(3, Planadquisicione::visibleTo($u)->count());
    }

    public function test_usuario_de_area_ve_solo_su_area(): void
    {
        $u = $this->user(['area_id' => $this->areaA1->id]);
        $this->assertSame(1, Planadquisicione::visibleTo($u)->count());
    }

    public function test_usuario_de_dependencia_ve_su_dependencia_y_sus_areas(): void
    {
        // Dep A: plan directo + plan del área A1 = 2
        $u = $this->user(['dependencia_id' => $this->depA->id]);
        $this->assertSame(2, Planadquisicione::visibleTo($u)->count());
    }

    public function test_usuario_sin_scope_no_ve_nada(): void
    {
        $u = $this->user();
        $this->assertSame(0, Planadquisicione::visibleTo($u)->count());
    }

    public function test_filtro_por_area_acota(): void
    {
        $u = $this->user([], 'super_admin');
        $count = Planadquisicione::visibleTo($u)
            ->applyDashboardFilters(['area_id' => $this->areaB1->id])
            ->count();
        $this->assertSame(1, $count);
    }
}
