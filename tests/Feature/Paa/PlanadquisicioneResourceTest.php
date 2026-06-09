<?php

namespace Tests\Feature\Paa;

use App\Filament\Resources\PlanadquisicioneResource\Pages\ListPlanadquisiciones;
use App\Models\{Area, Dependencia, Planadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Concerns\GrantsPaaPlanPermissions;
use Tests\TestCase;

class PlanadquisicioneResourceTest extends TestCase
{
    use GrantsPaaPlanPermissions;
    use RefreshDatabase;

    public function test_super_admin_ve_la_lista(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->grantPlanPermissions($admin);
        $this->actingAs($admin);

        Livewire::test(ListPlanadquisiciones::class)->assertSuccessful();
    }

    public function test_usuario_de_area_solo_ve_planes_de_su_area(): void
    {
        [$a1, $a2, $a3] = $this->setupAreas();
        $p1 = $this->plan($a1->id);
        $p2 = $this->plan($a2->id);
        $p3 = $this->plan($a3->id);

        // Usuario con área A1 (de la dependencia D1)
        $user = User::factory()->create(['area_id' => $a1->id, 'dependencia_id' => $a1->dependencia_id]);
        $this->grantPlanPermissions($user);
        $this->actingAs($user);

        Livewire::test(ListPlanadquisiciones::class)
            ->assertCanSeeTableRecords([$p1])
            ->assertCanNotSeeTableRecords([$p2, $p3]);
    }

    public function test_usuario_de_dependencia_ve_planes_de_todas_sus_areas(): void
    {
        [$a1, $a2, $a3] = $this->setupAreas();
        $p1 = $this->plan($a1->id);
        $p2 = $this->plan($a2->id);
        $p3 = $this->plan($a3->id);

        // Usuario sin área pero con dependencia D1 (dueña de A1 y A2)
        $user = User::factory()->create(['area_id' => null, 'dependencia_id' => $a1->dependencia_id]);
        $this->grantPlanPermissions($user);
        $this->actingAs($user);

        Livewire::test(ListPlanadquisiciones::class)
            ->assertCanSeeTableRecords([$p1, $p2])
            ->assertCanNotSeeTableRecords([$p3]);
    }

    public function test_ssst_ve_todos_los_planes(): void
    {
        Role::findOrCreate('SSST');
        [$a1, $a2, $a3] = $this->setupAreas();
        $p1 = $this->plan($a1->id);
        $p2 = $this->plan($a2->id);
        $p3 = $this->plan($a3->id);

        // SSST aunque tenga un área, ve todo
        $user = User::factory()->create(['area_id' => $a1->id]);
        $user->assignRole('SSST');
        $this->grantPlanPermissions($user);
        $this->actingAs($user);

        Livewire::test(ListPlanadquisiciones::class)
            ->assertCanSeeTableRecords([$p1, $p2, $p3]);
    }

    public function test_usuario_de_dependencia_ve_planes_a_nivel_de_dependencia(): void
    {
        [$a1, $a2, $a3] = $this->setupAreas();
        $depId = $a1->dependencia_id;

        // Plan registrado a nivel de dependencia (sin área específica).
        $planDep = Planadquisicione::create([
            'descripcioncont' => 'Plan a nivel dependencia',
            'valorestimadocont' => '1',
            'valorestimadovig' => '1',
            'duracont' => '1',
            'area_id' => null,
            'dependencia_id' => $depId,
        ]);
        // Plan de otra dependencia (vía su área).
        $planOtra = $this->plan($a3->id);

        $user = User::factory()->create(['area_id' => null, 'dependencia_id' => $depId]);
        $this->grantPlanPermissions($user);
        $this->actingAs($user);

        Livewire::test(ListPlanadquisiciones::class)
            ->assertCanSeeTableRecords([$planDep])
            ->assertCanNotSeeTableRecords([$planOtra]);
    }

    /** @return Area[] [A1(D1), A2(D1), A3(D2)] */
    private function setupAreas(): array
    {
        $d1 = Dependencia::create(['nombre' => 'Dependencia 1']);
        $d2 = Dependencia::create(['nombre' => 'Dependencia 2']);
        $a1 = Area::create(['nombre' => 'Área 1', 'codigo' => 'A1', 'dependencia_id' => $d1->id]);
        $a2 = Area::create(['nombre' => 'Área 2', 'codigo' => 'A2', 'dependencia_id' => $d1->id]);
        $a3 = Area::create(['nombre' => 'Área 3', 'codigo' => 'A3', 'dependencia_id' => $d2->id]);

        return [$a1, $a2, $a3];
    }

    private function plan(int $areaId): Planadquisicione
    {
        return Planadquisicione::create([
            'descripcioncont' => 'Plan del área ' . $areaId,
            'valorestimadocont' => '1',
            'valorestimadovig' => '1',
            'duracont' => '1',
            'area_id' => $areaId,
        ]);
    }
}
