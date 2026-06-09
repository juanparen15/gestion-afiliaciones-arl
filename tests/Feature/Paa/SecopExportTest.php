<?php

namespace Tests\Feature\Paa;

use App\Exports\PlanadquisicioneSecopExport;
use App\Models\Planadquisicione;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecopExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_secop_incluye_los_planes_de_la_vigencia(): void
    {
        $plan = Planadquisicione::create([
            'descripcioncont' => 'OBJETO CONTRACTUAL DE PRUEBA SECOP',
            'valorestimadocont' => '55.000.000',
            'valorestimadovig' => '55.000.000',
            'duracont' => '11',
            'id_vigencia' => 7,
        ]);

        $html = (new PlanadquisicioneSecopExport((int) date('Y')))->view()->render();

        $this->assertStringContainsString('OBJETO CONTRACTUAL DE PRUEBA SECOP', $html);
        $this->assertStringContainsString('Código UNSPSC', $html);
        $this->assertStringContainsString('55.000.000', $html);
        // Ubicación por defecto (DIVIPOLA Puerto Boyacá)
        $this->assertStringContainsString('CO-BOY-15572', $html);
    }
}
