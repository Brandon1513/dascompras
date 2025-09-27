<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
// (opcional) use Database\Seeders\NivelesAprobacionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1) Niveles de aprobación
        $this->call(\Database\Seeders\NivelesAprobacionSeeder::class);

        // 2) (Opcional) Crear un usuario de prueba
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
            // password se toma del factory por defecto
        ]);
    }
}
