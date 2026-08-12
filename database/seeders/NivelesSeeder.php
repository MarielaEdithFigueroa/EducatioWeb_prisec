<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelesSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['codigo' => 'INICIAL', 'descripcion' => 'Inicial', 'activo' => true],
            ['codigo' => 'PRIMARIA', 'descripcion' => 'Primaria', 'activo' => true],
            ['codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria', 'activo' => true],
        ];

        foreach ($niveles as $nivel) {
            Nivel::query()->updateOrCreate(
                ['codigo' => $nivel['codigo']],
                $nivel,
            );
        }
    }
}
