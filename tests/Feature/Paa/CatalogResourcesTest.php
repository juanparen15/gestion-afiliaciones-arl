<?php

namespace Tests\Feature\Paa;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogResourcesTest extends TestCase
{
    use RefreshDatabase;

    /** Página List => slug del modelo para el permiso view_any_{slug} */
    private array $paginas = [
        \App\Filament\Resources\SegmentoResource\Pages\ListSegmentos::class => 'segmento',
        \App\Filament\Resources\FamiliaResource\Pages\ListFamilias::class => 'familia',
        \App\Filament\Resources\ClaseResource\Pages\ListClases::class => 'clase',
        \App\Filament\Resources\ProductoResource\Pages\ListProductos::class => 'producto',
        \App\Filament\Resources\EstadovigenciaResource\Pages\ListEstadovigencias::class => 'estadovigencia',
        \App\Filament\Resources\MeseResource\Pages\ListMeses::class => 'mese',
        \App\Filament\Resources\ModalidadeResource\Pages\ListModalidades::class => 'modalidade',
        \App\Filament\Resources\IntervaloResource\Pages\ListIntervalos::class => 'intervalo',
        \App\Filament\Resources\VigenfuturaResource\Pages\ListVigenfuturas::class => 'vigenfutura',
        \App\Filament\Resources\TipozonaResource\Pages\ListTipozonas::class => 'tipozona',
        \App\Filament\Resources\TipoprocesoResource\Pages\ListTipoprocesos::class => 'tipoproceso',
        \App\Filament\Resources\TipoadquisicioneResource\Pages\ListTipoadquisiciones::class => 'tipoadquisicione',
        \App\Filament\Resources\RequiproyectoResource\Pages\ListRequiproyectos::class => 'requiproyecto',
        \App\Filament\Resources\FuenteResource\Pages\ListFuentes::class => 'fuente',
        \App\Filament\Resources\TipoprioridadeResource\Pages\ListTipoprioridades::class => 'tipoprioridade',
        \App\Filament\Resources\RequipoaiResource\Pages\ListRequipoais::class => 'requipoai',
    ];

    public function test_super_admin_con_permisos_ve_los_catalogos(): void
    {
        $superAdmin = Role::findOrCreate('super_admin');
        foreach ($this->paginas as $slug) {
            $superAdmin->givePermissionTo(Permission::findOrCreate("view_any_{$slug}"));
        }

        $admin = User::factory()->create();
        $admin->assignRole('super_admin');
        $this->actingAs($admin);

        foreach (array_keys($this->paginas) as $page) {
            Livewire::test($page)->assertSuccessful();
        }
    }

    public function test_rol_dependencia_sin_permisos_no_ve_los_catalogos(): void
    {
        Role::findOrCreate('Dependencia');
        // Aseguramos que los permisos existen pero NO se asignan a Dependencia.
        foreach ($this->paginas as $slug) {
            Permission::findOrCreate("view_any_{$slug}");
        }

        $dep = User::factory()->create();
        $dep->assignRole('Dependencia');
        $this->actingAs($dep);

        foreach (array_keys($this->paginas) as $page) {
            Livewire::test($page)->assertForbidden();
        }
    }
}
