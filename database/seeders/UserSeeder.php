<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Crea 5 usuarios adicionales: algunos como EMPLOYEE y otros como AREA_MANAGER,
 * para poder asignar gerentes de área desde la vista de Áreas.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (\Spatie\Permission\Models\Role::where('name', 'EMPLOYEE')->doesntExist()) {
            $this->call(RoleSeeder::class);
        }

        $users = [
            ['name' => 'Ana García', 'email' => 'ana.garcia@example.com', 'role' => 'EMPLOYEE'],
            ['name' => 'Carlos López', 'email' => 'carlos.lopez@example.com', 'role' => 'EMPLOYEE'],
            ['name' => 'María Ruiz', 'email' => 'maria.ruiz@example.com', 'role' => 'AREA_MANAGER'],
            ['name' => 'Pedro Sánchez', 'email' => 'pedro.sanchez@example.com', 'role' => 'AREA_MANAGER'],
            ['name' => 'Laura Martínez', 'email' => 'laura.martinez@example.com', 'role' => 'EMPLOYEE'],
        ];

        $password = Hash::make('password');

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $password,
                ]
            );
            if (! $user->hasRole($data['role'])) {
                $user->assignRole($data['role']);
            }
        }
    }
}
