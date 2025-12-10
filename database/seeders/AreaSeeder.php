<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['id' => 7, 'dependencia_id' => 6, 'nombre' => 'JURIDICA', 'codigo' => 'J', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 8, 'dependencia_id' => 8, 'nombre' => 'AREA DE PERSONAL', 'codigo' => 'AP', 'descripcion' => null, 'responsable' => null, 'email' => 'personal@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 9, 'dependencia_id' => 8, 'nombre' => 'ALMACEN  MUNICIPAL ', 'codigo' => 'AM', 'descripcion' => null, 'responsable' => null, 'email' => 'almacen@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 10, 'dependencia_id' => 8, 'nombre' => 'AREA DE SISTEMAS', 'codigo' => 'AS', 'descripcion' => null, 'responsable' => 'FABIAN MURILLO MARIN', 'email' => 'sistemas@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 11, 'dependencia_id' => 8, 'nombre' => 'VENTANILLA UNICA', 'codigo' => 'VU', 'descripcion' => null, 'responsable' => null, 'email' => 'contactenos@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 12, 'dependencia_id' => 8, 'nombre' => 'ARCHIVO CENTRAL', 'codigo' => 'AC', 'descripcion' => null, 'responsable' => null, 'email' => 'archivo@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 13, 'dependencia_id' => 9, 'nombre' => 'BOMBEROS', 'codigo' => 'B', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 14, 'dependencia_id' => 9, 'nombre' => 'INSPECCION DE POLICIA URBANA', 'codigo' => 'IPU', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 15, 'dependencia_id' => 9, 'nombre' => 'INSPECCION DE POLICIA RURAL - VEREDA  EL MARFIL', 'codigo' => 'IPUVM', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 16, 'dependencia_id' => 9, 'nombre' => 'INSPECCION DE POLICIA RURAL - VEREDA  PUERTO ROMERO', 'codigo' => 'IPRVPR', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 17, 'dependencia_id' => 9, 'nombre' => 'INSPECCION DE POLICIA RURAL  - KILOMETRO 25', 'codigo' => 'IPRK', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 18, 'dependencia_id' => 9, 'nombre' => 'COMISARIA DE FAMILIA', 'codigo' => 'CF', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 19, 'dependencia_id' => 9, 'nombre' => 'CORREGIMIENTO DE VASCONIA', 'codigo' => 'CV', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 20, 'dependencia_id' => 9, 'nombre' => 'CORREGIMIENTO DE PUERTO PINZON', 'codigo' => 'CPP', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 21, 'dependencia_id' => 10, 'nombre' => 'CASA DE LA CULTURA - GUILLERMO CANO ISAZA', 'codigo' => 'CCGCI', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 22, 'dependencia_id' => 10, 'nombre' => 'SISBEN', 'codigo' => 'S', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 23, 'dependencia_id' => 10, 'nombre' => 'MAS FAMILIAS EN ACCION', 'codigo' => 'MFA', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 24, 'dependencia_id' => 10, 'nombre' => 'ADULTO MAYOR', 'codigo' => 'ADM', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 25, 'dependencia_id' => 10, 'nombre' => 'SALUD', 'codigo' => 'SA', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 26, 'dependencia_id' => 11, 'nombre' => 'VIVIENDA', 'codigo' => 'V', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 27, 'dependencia_id' => 11, 'nombre' => 'BANCO DE PROYECTOS', 'codigo' => 'BP', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 28, 'dependencia_id' => 12, 'nombre' => 'PRESUPUESTO', 'codigo' => 'P', 'descripcion' => null, 'responsable' => null, 'email' => 'presupuesto@puertoboyaca-boyaca.gov.co', 'telefono' => null, 'activo' => true],
            ['id' => 29, 'dependencia_id' => 12, 'nombre' => 'CONTABILIDAD', 'codigo' => 'C', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 30, 'dependencia_id' => 12, 'nombre' => 'PREDIAL', 'codigo' => 'PR', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 31, 'dependencia_id' => 12, 'nombre' => 'TESORERIA', 'codigo' => 'T', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
            ['id' => 32, 'dependencia_id' => 13, 'nombre' => 'AREA DE CONTRAVENCIONES', 'codigo' => 'ADC', 'descripcion' => null, 'responsable' => null, 'email' => null, 'telefono' => null, 'activo' => true],
        ];

        foreach ($areas as $areaData) {
            Area::updateOrCreate(
                ['id' => $areaData['id']],
                $areaData
            );
        }

        $this->command->info('Áreas creadas exitosamente');
    }
}
