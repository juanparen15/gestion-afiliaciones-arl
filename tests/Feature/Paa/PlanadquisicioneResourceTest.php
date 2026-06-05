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
        Planadquisicione::create(['descripcioncont' => 'mio', 'valorestimadocont' => '1', 'valorestimadovig' => '1', 'duracont' => '1', 'user_id' => $u1->id]);
        Planadquisicione::create(['descripcioncont' => 'ajeno', 'valorestimadocont' => '1', 'valorestimadovig' => '1', 'duracont' => '1', 'user_id' => $u2->id]);

        $this->actingAs($u1);
        Livewire::test(ListPlanadquisiciones::class)
            ->assertCanSeeTableRecords(Planadquisicione::where('user_id', $u1->id)->get())
            ->assertCanNotSeeTableRecords(Planadquisicione::where('user_id', $u2->id)->get());
    }
}
