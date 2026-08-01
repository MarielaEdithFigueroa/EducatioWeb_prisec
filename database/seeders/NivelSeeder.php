<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class NivelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Nivel::query()->upsert([
            ['activo' => true, 'codigo' => 'PRI', 'descripcion' => 'Primaria', 'orden' => 1],
            ['activo' => true, 'codigo' => 'SEC', 'descripcion' => 'Secundaria', 'orden' => 2],
        ], ['codigo'], ['activo', 'descripcion', 'orden']);
    }
}
