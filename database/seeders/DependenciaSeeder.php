<?php

namespace Database\Seeders;

use App\Models\Dependencia;
use Illuminate\Database\Seeder;

class DependenciaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dependencias = [
            [
                'id' => 6,
                'nombre' => 'DESPACHO',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => 'JHON FEIBER URREA CIFUENTES',
                'email' => 'alcalde@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 7,
                'nombre' => 'CONTROL IINTERNO',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'controlinterno@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 8,
                'nombre' => 'SECRETARIA GENERAL Y DE SERVICIOS ADMINISTRATIVOS',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'general@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 9,
                'nombre' => 'SECRETARIA DE GOBIERNO MUNICIPAL Y CONVIVENCIA CIUDADANA',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'gobierno@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 10,
                'nombre' => 'SECRETARIA DE DESARROLLO SOCIAL Y COMUNITARIO',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'desarrollo@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 11,
                'nombre' => 'SECRETARIA DE PLANEACION MUNICIPAL',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'planeacion@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 12,
                'nombre' => 'SECRETARIA DE HACIENDA',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'hacienda@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 13,
                'nombre' => 'INSPECCIÓN DE TRANSITO Y TRANSPORTE',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'transito@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
            [
                'id' => 14,
                'nombre' => 'UNIDAD DE ASISTENCIA TECNICA -UMATA',
                'codigo' => null,
                'descripcion' => null,
                'responsable' => null,
                'email' => 'umata@puertoboyaca-boyaca.gov.co',
                'telefono' => null,
                'activo' => true,
            ],
        ];

        foreach ($dependencias as $dependenciaData) {
            Dependencia::updateOrCreate(
                ['id' => $dependenciaData['id']],
                $dependenciaData
            );
        }

        $this->command->info('Dependencias creadas exitosamente');
    }
}
