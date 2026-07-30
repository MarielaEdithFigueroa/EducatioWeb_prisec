<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NivelesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $niveles = [
            ['codigo' => 'PRI', 'descripcion' => 'Primaria'],
            ['codigo' => 'SEC', 'descripcion' => 'Secundaria'],
        ];

        foreach ($niveles as $nivel) {
            Nivel::factory()->create($nivel);
        }
    }
}
