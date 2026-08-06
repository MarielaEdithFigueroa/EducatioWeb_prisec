<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelesSeeder extends Seeder
{
    public function run(): void
    {
        $niveles = [
            ['id_nivel' => 1, 'codigo' => 'INICIAL', 'descripcion' => 'Inicial'],
            ['id_nivel' => 2, 'codigo' => 'PRIMARIA', 'descripcion' => 'Primaria'],
            ['id_nivel' => 3, 'codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria'],
        ];

        foreach ($niveles as $nivel) {
            Nivel::query()->updateOrCreate(
                ['id_nivel' => $nivel['id_nivel']],
                $nivel,
            );
        }
    }
}
