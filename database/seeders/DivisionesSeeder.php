<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Seeder;

class DivisionesSeeder extends Seeder
{
    public function run(): void
    {
        $divisiones = [
            ['descripcion' => 'A', 'activo' => true, 'orden' => 1],
            ['descripcion' => 'B', 'activo' => true, 'orden' => 2],
        ];

        foreach ($divisiones as $division) {
            Division::query()->updateOrCreate(
                ['descripcion' => $division['descripcion']],
                $division,
            );
        }
    }
}
