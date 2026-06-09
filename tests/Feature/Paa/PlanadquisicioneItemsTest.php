<?php

namespace Tests\Feature\Paa;

use App\Filament\Resources\PlanadquisicioneResource\Pages\CreatePlanadquisicione;
use App\Models\{Clase, Familia, Planadquisicione, Producto, Segmento, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Concerns\GrantsPaaPlanPermissions;
use Tests\TestCase;

class PlanadquisicioneItemsTest extends TestCase
{
    use GrantsPaaPlanPermissions;
    use RefreshDatabase;

    public function test_plan_tiene_items_clase_con_producto_opcional(): void
    {
        $seg = Segmento::create(['detsegmento' => 'S']);
        $fam = Familia::create(['detfamilia' => 'F', 'segmento_id' => $seg->id]);
        $cla = Clase::create(['detclase' => 'C', 'familia_id' => $fam->id]);
        $pro = Producto::create(['detproducto' => 'P', 'clase_id' => $cla->id]);

        $plan = Planadquisicione::create([
            'descripcioncont' => 'X',
            'valorestimadocont' => '1',
            'valorestimadovig' => '1',
            'duracont' => '1',
        ]);

        // Una línea solo con clase (producto opcional → null) y otra con producto.
        $plan->items()->create(['clase_id' => $cla->id, 'producto_id' => null]);
        $plan->items()->create(['clase_id' => $cla->id, 'producto_id' => $pro->id]);

        $this->assertEquals(2, $plan->items()->count());
        $this->assertEquals($cla->id, $plan->items()->first()->clase->id);
        $this->assertEquals(
            $pro->id,
            $plan->items()->whereNotNull('producto_id')->first()->producto->id
        );
    }

    public function test_pagina_de_creacion_monta_el_repeater(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->grantPlanPermissions($admin);
        $this->actingAs($admin);

        Livewire::test(CreatePlanadquisicione::class)->assertSuccessful();
    }

    public function test_vista_comprobante_muestra_clasificacion_unspsc(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->grantPlanPermissions($admin);
        $this->actingAs($admin);

        $seg = Segmento::create(['detsegmento' => 'Seg X']);
        $fam = Familia::create(['detfamilia' => 'Fam X', 'segmento_id' => $seg->id]);
        $cla = Clase::create(['detclase' => 'Cla X', 'familia_id' => $fam->id]);
        $pro = Producto::create(['detproducto' => 'Prod X', 'clase_id' => $cla->id]);

        $plan = Planadquisicione::create([
            'descripcioncont' => 'Plan de prueba',
            'valorestimadocont' => '1',
            'valorestimadovig' => '1',
            'duracont' => '1',
        ]);
        // Fila que solo trae producto_id (sin clase_id): debe reconstruir la cascada.
        $plan->items()->create(['clase_id' => null, 'producto_id' => $pro->id]);

        Livewire::test(\App\Filament\Resources\PlanadquisicioneResource\Pages\ViewPlanadquisicione::class, ['record' => $plan->getRouteKey()])
            ->assertSuccessful()
            ->assertSee('Seg X')
            ->assertSee('Fam X')
            ->assertSee('Cla X')
            ->assertSee('Prod X');
    }
}
