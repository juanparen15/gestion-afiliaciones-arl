<?php

namespace Database\Seeders;

use App\Models\Dependencia;
use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear dependencias de ejemplo
        $dependencias = [
            ['nombre' => 'Sistemas e Informática', 'codigo' => 'SIS', 'activo' => true],
            ['nombre' => 'Talento Humano', 'codigo' => 'TH', 'activo' => true],
            ['nombre' => 'Seguridad y Salud en el Trabajo', 'codigo' => 'SST', 'activo' => true],
            ['nombre' => 'Administrativa', 'codigo' => 'ADM', 'activo' => true],
            ['nombre' => 'Financiera', 'codigo' => 'FIN', 'activo' => true],
        ];

        foreach ($dependencias as $dep) {
            Dependencia::create($dep);
        }

        // Crear áreas de ejemplo para cada dependencia
        $sistemasDep = Dependencia::where('codigo', 'SIS')->first();
        $areas = [
            [
                'dependencia_id' => $sistemasDep->id,
                'nombre' => 'Área de Sistemas',
                'codigo' => 'SIS-SIS',
                'descripcion' => 'Gestión de sistemas de información y tecnología',
                'activo' => true,
            ],
            [
                'dependencia_id' => $sistemasDep->id,
                'nombre' => 'Área de Contratación',
                'codigo' => 'SIS-CON',
                'descripcion' => 'Gestión de procesos contractuales',
                'activo' => true,
            ],
            [
                'dependencia_id' => $sistemasDep->id,
                'nombre' => 'Área de Archivo',
                'codigo' => 'SIS-ARC',
                'descripcion' => 'Gestión documental y archivo general',
                'activo' => true,
            ],
            [
                'dependencia_id' => $sistemasDep->id,
                'nombre' => 'Área de Almacén',
                'codigo' => 'SIS-ALM',
                'descripcion' => 'Gestión de almacén e inventarios',
                'activo' => true,
            ],
        ];

        foreach ($areas as $area) {
            Area::create($area);
        }

        // Crear áreas para Talento Humano
        $talentoDep = Dependencia::where('codigo', 'TH')->first();
        Area::create([
            'dependencia_id' => $talentoDep->id,
            'nombre' => 'Área de Nómina',
            'codigo' => 'TH-NOM',
            'descripcion' => 'Gestión de nómina y pagos',
            'activo' => true,
        ]);

        Area::create([
            'dependencia_id' => $talentoDep->id,
            'nombre' => 'Área de Selección',
            'codigo' => 'TH-SEL',
            'descripcion' => 'Procesos de selección y vinculación',
            'activo' => true,
        ]);

        // Crear roles principales
        $adminRole = Role::create(['name' => 'super_admin']);
        $dependenciaRole = Role::create(['name' => 'Dependencia']);
        $sstRole = Role::create(['name' => 'SSST']);

        // Crear usuario administrador
        $admin = User::create([
            'name' => 'Administrador del Sistema',
            'email' => 'admin@arl.gov.co',
            'correo_institucional' => 'admin@entidad.gov.co',
            'cargo' => 'Administrador de Sistema',
            'dependencia_id' => 1,
            'password' => bcrypt('password123'),
        ]);
        $admin->assignRole('Administrador');

        // Usuario SSST de ejemplo
        $sst = User::create([
            'name' => 'Coordinador SST',
            'email' => 'sst@arl.gov.co',
            'correo_institucional' => 'sst@entidad.gov.co',
            'cargo' => 'Coordinador de Seguridad y Salud',
            'dependencia_id' => 3,
            'password' => bcrypt('password123'),
        ]);
        $sst->assignRole('SSST');

        // Usuario Dependencia de ejemplo
        $dep = User::create([
            'name' => 'Jefe de Dependencia',
            'email' => 'dependencia@arl.gov.co',
            'correo_institucional' => 'dependencia@entidad.gov.co',
            'cargo' => 'Jefe de Dependencia',
            'dependencia_id' => 2,
            'password' => bcrypt('password123'),
        ]);
        $dep->assignRole('Dependencia');
    }
}
