<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelesSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['id' => 1, 'codigo' => 'INICIAL', 'descripcion' => 'Inicial'],
            ['id' => 2, 'codigo' => 'PRIMARIA', 'descripcion' => 'Primaria'],
            ['id' => 3, 'codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria'],
        ];

        foreach ($niveles as $nivel) {
            Nivel::query()->updateOrCreate(
                ['id' => $nivel['id']],
                $nivel,
            );
        }
    }
}
