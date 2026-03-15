<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Roles obligatorios del sistema de gestión de vacaciones.
     */
    private const ROLES = [
        'ADMIN',
        'HR_MANAGER',
        'AREA_MANAGER',
        'EMPLOYEE',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guardName = config('auth.defaults.guard', 'web');

        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => $guardName],
                ['name' => $roleName, 'guard_name' => $guardName]
            );
        }
    }
}
