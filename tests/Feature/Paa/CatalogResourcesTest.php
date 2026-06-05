<?php

namespace Tests\Feature\Paa;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogResourcesTest extends TestCase
{
    use RefreshDatabase;

    public function test_paginas_index_catalogos_cargan(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        $pages = [
            \App\Filament\Resources\SegmentoResource\Pages\ListSegmentos::class,
            \App\Filament\Resources\FamiliaResource\Pages\ListFamilias::class,
            \App\Filament\Resources\ClaseResource\Pages\ListClases::class,
            \App\Filament\Resources\ProductoResource\Pages\ListProductos::class,
            \App\Filament\Resources\EstadovigenciaResource\Pages\ListEstadovigencias::class,
            \App\Filament\Resources\MeseResource\Pages\ListMeses::class,
            \App\Filament\Resources\ModalidadeResource\Pages\ListModalidades::class,
            \App\Filament\Resources\IntervaloResource\Pages\ListIntervalos::class,
            \App\Filament\Resources\VigenfuturaResource\Pages\ListVigenfuturas::class,
            \App\Filament\Resources\TipozonaResource\Pages\ListTipozonas::class,
            \App\Filament\Resources\TipoprocesoResource\Pages\ListTipoprocesos::class,
            \App\Filament\Resources\TipoadquisicioneResource\Pages\ListTipoadquisiciones::class,
            \App\Filament\Resources\RequiproyectoResource\Pages\ListRequiproyectos::class,
            \App\Filament\Resources\FuenteResource\Pages\ListFuentes::class,
            \App\Filament\Resources\TipoprioridadeResource\Pages\ListTipoprioridades::class,
            \App\Filament\Resources\RequipoaiResource\Pages\ListRequipoais::class,
        ];
        foreach ($pages as $page) {
            Livewire::test($page)->assertSuccessful();
        }
    }
}
