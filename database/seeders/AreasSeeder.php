<?php

namespace Database\Seeders;

use App\Models\Dependencia;
use App\Models\Area;
use Illuminate\Database\Seeder;

class AreasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear áreas de ejemplo para Sistemas e Informática
        $sistemasDep = Dependencia::where('codigo', 'SIS')->first();

        if ($sistemasDep) {
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
                Area::updateOrCreate(
                    ['codigo' => $area['codigo']],
                    $area
                );
            }
        }

        // Crear áreas para Talento Humano
        $talentoDep = Dependencia::where('codigo', 'TH')->first();

        if ($talentoDep) {
            Area::updateOrCreate(
                ['codigo' => 'TH-NOM'],
                [
                    'dependencia_id' => $talentoDep->id,
                    'nombre' => 'Área de Nómina',
                    'codigo' => 'TH-NOM',
                    'descripcion' => 'Gestión de nómina y pagos',
                    'activo' => true,
                ]
            );

            Area::updateOrCreate(
                ['codigo' => 'TH-SEL'],
                [
                    'dependencia_id' => $talentoDep->id,
                    'nombre' => 'Área de Selección',
                    'codigo' => 'TH-SEL',
                    'descripcion' => 'Procesos de selección y vinculación',
                    'activo' => true,
                ]
            );
        }

        // Crear áreas para Seguridad y Salud en el Trabajo
        $sstDep = Dependencia::where('codigo', 'SST')->first();

        if ($sstDep) {
            Area::updateOrCreate(
                ['codigo' => 'SST-PRE'],
                [
                    'dependencia_id' => $sstDep->id,
                    'nombre' => 'Área de Prevención',
                    'codigo' => 'SST-PRE',
                    'descripcion' => 'Prevención de riesgos laborales',
                    'activo' => true,
                ]
            );

            Area::updateOrCreate(
                ['codigo' => 'SST-ARL'],
                [
                    'dependencia_id' => $sstDep->id,
                    'nombre' => 'Área de ARL',
                    'codigo' => 'SST-ARL',
                    'descripcion' => 'Gestión de afiliaciones a ARL',
                    'activo' => true,
                ]
            );
        }

        $this->command->info('Áreas creadas exitosamente.');
    }
}
