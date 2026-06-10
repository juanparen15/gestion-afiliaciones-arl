<?php

namespace Tests\Feature\Paa;

use App\Filament\Widgets\PaaStatsOverview;
use App\Filament\Widgets\PlanesPorAreaChart;
use App\Filament\Widgets\PlanesPorMesChart;
use App\Filament\Widgets\PlanesPorTipoAdquisicionChart;
use App\Filament\Widgets\PlanesValorPorDependenciaChart;
use App\Filament\Widgets\PlanesVinculadosContratoChart;
use App\Models\{Area, Dependencia, Mese, Planadquisicione, Tipoadquisicione, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaaWidgetsTest extends TestCase
{
    use RefreshDatabase;

    /** @return class-string[] */
    private function todosLosWidgets(): array
    {
        return [
            PaaStatsOverview::class,
            PlanesValorPorDependenciaChart::class,
            PlanesPorAreaChart::class,
            PlanesPorTipoAdquisicionChart::class,
            PlanesPorMesChart::class,
            PlanesVinculadosContratoChart::class,
        ];
    }

    public function test_widgets_renderizan_sin_datos(): void
    {
        $this->actingAsAdmin();

        foreach ($this->todosLosWidgets() as $widget) {
            Livewire::test($widget)->assertSuccessful();
        }
    }

    public function test_widgets_renderizan_con_datos_y_filtros(): void
    {
        $this->actingAsAdmin();

        $dep = Dependencia::create(['nombre' => 'Dep']);
        $area = Area::create(['nombre' => 'Planeación', 'codigo' => 'PLA', 'dependencia_id' => $dep->id]);
        $mes = Mese::create(['nommes' => 'Enero']);
        $tipo = Tipoadquisicione::create(['dettipoadquisicion' => 'Bienes']);

        Planadquisicione::create([
            'descripcioncont' => 'P', 'valorestimadocont' => '55.000.000',
            'valorestimadovig' => '55.000.000', 'duracont' => '11',
            'area_id' => $area->id, 'dependencia_id' => $dep->id,
            'mese_id' => $mes->id, 'tipoadquisicione_id' => $tipo->id,
        ]);

        // ChartWidget ejecuta getData() en mount() (vía generateDataChecksum),
        // así que el render ya ejercita la consulta de cada widget.
        $filtros = ['filters' => ['vigencia' => (int) date('Y'), 'area_id' => $area->id]];

        foreach ($this->todosLosWidgets() as $widget) {
            Livewire::test($widget, $filtros)->assertSuccessful();
        }
    }

    public function test_valor_por_dependencia_solo_visible_para_quien_ve_todo(): void
    {
        Role::findOrCreate('super_admin');
        Role::findOrCreate('Dependencia');

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);
        $this->assertTrue(PlanesValorPorDependenciaChart::canView());

        $depUser = User::factory()->create();
        $depUser->assignRole('Dependencia');
        $this->actingAs($depUser);
        $this->assertFalse(PlanesValorPorDependenciaChart::canView());
    }

    private function actingAsAdmin(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);
    }
}
