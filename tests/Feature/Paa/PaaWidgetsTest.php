<?php

namespace Tests\Feature\Paa;

use App\Filament\Widgets\{PaaStatsOverview, PlanesPorAreaChart, PlanesPorMesChart};
use App\Models\{Area, Dependencia, Mese, Planadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaaWidgetsTest extends TestCase
{
    use RefreshDatabase;

    public function test_widgets_renderizan_sin_datos(): void
    {
        $this->actingAsAdmin();

        Livewire::test(PaaStatsOverview::class)->assertSuccessful();
        Livewire::test(PlanesPorAreaChart::class)->assertSuccessful();
        Livewire::test(PlanesPorMesChart::class)->assertSuccessful();
    }

    public function test_widgets_renderizan_con_datos(): void
    {
        $this->actingAsAdmin();

        $dep = Dependencia::create(['nombre' => 'Dep']);
        $area = Area::create(['nombre' => 'Planeación', 'codigo' => 'PLA', 'dependencia_id' => $dep->id]);
        $mes = Mese::create(['nommes' => 'Enero']);
        Planadquisicione::create([
            'descripcioncont' => 'P', 'valorestimadocont' => '55.000.000',
            'valorestimadovig' => '55.000.000', 'duracont' => '11',
            'area_id' => $area->id, 'mese_id' => $mes->id,
        ]);

        Livewire::test(PaaStatsOverview::class)->assertSuccessful();
        Livewire::test(PlanesPorAreaChart::class)->assertSuccessful();
        Livewire::test(PlanesPorMesChart::class)->assertSuccessful();
    }

    private function actingAsAdmin(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);
    }
}
