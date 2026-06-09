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
