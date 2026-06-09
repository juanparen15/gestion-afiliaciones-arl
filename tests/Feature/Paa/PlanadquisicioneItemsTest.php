<?php

namespace Tests\Feature\Paa;

use App\Filament\Resources\PlanadquisicioneResource\Pages\CreatePlanadquisicione;
use App\Models\{Clase, Familia, Planadquisicione, Producto, Segmento, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PlanadquisicioneItemsTest extends TestCase
{
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
        $this->actingAs($admin);

        Livewire::test(CreatePlanadquisicione::class)->assertSuccessful();
    }
}
