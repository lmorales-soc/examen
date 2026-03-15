<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    /**
     * Departamentos/áreas iniciales del sistema.
     */
    public function run(): void
    {
        $areas = [
            ['name' => 'Desarrollo', 'slug' => 'desarrollo'],
            ['name' => 'QA', 'slug' => 'qa'],
            ['name' => 'Infraestructura', 'slug' => 'infraestructura'],
            ['name' => 'Base de Datos', 'slug' => 'base-de-datos'],
            ['name' => 'Pagos', 'slug' => 'pagos'],
        ];

        foreach ($areas as $data) {
            Area::firstOrCreate(
                ['slug' => $data['slug']],
                ['name' => $data['name']]
            );
        }
    }
}
