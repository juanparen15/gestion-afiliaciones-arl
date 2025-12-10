<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrador del Sistema',
                'email' => 'ticsistemasptoboy@gmail.com',
                'correo_institucional' => 'sistemas@puertoboyaca-boyaca.gov.co',
                'cargo' => 'Administrador de Sistema',
                'dependencia_id' => null,
                'area_id' => null,
                'password' => Hash::make('password'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Coordinador SST',
                'email' => 'seguridadysalud@puertoboyaca-boyaca.gov.co',
                'correo_institucional' => 'seguridadysalud@puertoboyaca-boyaca.gov.co',
                'cargo' => 'Coordinador de Seguridad y Salud',
                'dependencia_id' => 8,
                'area_id' => 8,
                'password' => Hash::make('password'),
                'role' => 'SSST',
            ],
            [
                'name' => 'Fabian Murillo Marin',
                'email' => 'sistemas@puertoboyaca-boyaca.gov.co',
                'correo_institucional' => 'sistemas@puertoboyaca-boyaca.gov.co',
                'cargo' => 'Supervisor de Área',
                'dependencia_id' => 8,
                'area_id' => 10,
                'password' => Hash::make('password'),
                'role' => 'Dependencia',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            // Verificar si el usuario existe
            $existingUser = User::where('email', $userData['email'])->first();

            if ($existingUser) {
                // Si existe, solo actualizar campos si es necesario
                $existingUser->update([
                    'name' => $userData['name'],
                    'correo_institucional' => $userData['correo_institucional'],
                    'cargo' => $userData['cargo'],
                    'dependencia_id' => $userData['dependencia_id'],
                    'area_id' => $userData['area_id'],
                ]);
                $user = $existingUser;
            } else {
                // Si no existe, crear nuevo usuario
                $user = User::create($userData);
            }

            // Asignar rol al usuario
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }

        $this->command->info('Usuarios creados exitosamente');
        $this->command->warn('IMPORTANTE: Todos los usuarios tienen la contraseña por defecto: "password"');
        $this->command->warn('Por favor, cambia las contraseñas después del primer login.');
    }
}
