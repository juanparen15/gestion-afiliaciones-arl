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
        $plan = Planadquisicione::create([
            'descripcioncont' => 'D',
            'valorestimadocont' => '1',
            'valorestimadovig' => '1',
            'duracont' => '1',
        ]);

        // La tabla contratos no tiene columnas NOT NULL sin default (aparte de la PK),
        // por lo que un contrato mínimo es válido.
        $contrato = Contrato::create([
            'numero_contrato' => 'C-001',
            'objeto' => 'Objeto de prueba',
            'planadquisicione_id' => $plan->id,
        ]);

        $this->assertEquals(1, $plan->contratos()->count());
        $this->assertEquals($plan->id, $contrato->planadquisicione->id);
    }
}
