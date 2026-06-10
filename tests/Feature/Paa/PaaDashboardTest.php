<?php

namespace Tests\Feature\Paa;

use App\Filament\Pages\PaaDashboard;
use App\Models\Planadquisicione;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaaDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_pagina_carga_para_usuario_con_permiso(): void
    {
        Role::findOrCreate('super_admin');
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $admin->givePermissionTo(Permission::findOrCreate('view_any_planadquisicione'));
        $this->actingAs($admin);

        $this->get(PaaDashboard::getUrl())->assertSuccessful();
    }

    public function test_vigencia_actual_es_el_anio_mas_reciente_con_datos(): void
    {
        Planadquisicione::create([
            'descripcioncont' => 'Viejo', 'valorestimadocont' => '1.000.000',
            'valorestimadovig' => '1.000.000', 'duracont' => '6',
            'created_at' => '2021-03-01 00:00:00',
        ]);
        $reciente = Planadquisicione::create([
            'descripcioncont' => 'Nuevo', 'valorestimadocont' => '1.000.000',
            'valorestimadovig' => '1.000.000', 'duracont' => '6',
        ]);
        $reciente->forceFill(['created_at' => '2024-09-01 00:00:00'])->save();

        $this->assertSame(2024, PaaDashboard::vigenciaActual());
    }
}
