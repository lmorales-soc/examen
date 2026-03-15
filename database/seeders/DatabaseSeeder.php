<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            AreaSeeder::class,
            UserSeeder::class,
        ]);

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
            ]
        );
        if (! $adminUser->hasRole('ADMIN')) {
            $adminUser->assignRole('ADMIN');
        }

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Asegurar que el usuario con ID 1 también tenga rol ADMIN (por si es el que usas como admin)
        $userOne = User::find(1);
        if ($userOne && ! $userOne->hasRole('ADMIN')) {
            $userOne->assignRole('ADMIN');
        }
    }
}
