<?php

namespace Tests\Feature\Paa;

use App\Filament\Resources\PlanadquisicioneResource;
use App\Models\Tipoproceso;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TipoProcesoAutoTest extends TestCase
{
    use RefreshDatabase;

    public function test_autoselecciona_tipo_proceso_por_cuantia(): void
    {
        $minima = Tipoproceso::create(['dettipoproceso' => 'Mínima cuantía ($1 hasta $36.400.000)']);
        $menor = Tipoproceso::create(['dettipoproceso' => 'Menor cuantía ($36.400.001 hasta $364.000.000)']);
        $mayor = Tipoproceso::create(['dettipoproceso' => 'Mayor cuantía (Superiores a $364.000.001)']);

        $this->assertEquals($minima->id, PlanadquisicioneResource::tipoProcesoSegunValor('20.000.000'));
        $this->assertEquals($minima->id, PlanadquisicioneResource::tipoProcesoSegunValor('36.400.000'));
        $this->assertEquals($menor->id, PlanadquisicioneResource::tipoProcesoSegunValor('39.600.000'));
        $this->assertEquals($menor->id, PlanadquisicioneResource::tipoProcesoSegunValor('364.000.000'));
        $this->assertEquals($mayor->id, PlanadquisicioneResource::tipoProcesoSegunValor('500.000.000'));
        $this->assertNull(PlanadquisicioneResource::tipoProcesoSegunValor('0'));
    }
}
