<?php

namespace Database\Seeders;

use App\Models\Turno;
use Illuminate\Database\Seeder;

class TurnosSeeder extends Seeder
{
    public function run(): void
    {
        $turnos = [
            ['descripcion' => 'Mañana', 'activo' => true, 'orden' => 1],
            ['descripcion' => 'Tarde', 'activo' => true, 'orden' => 2],
        ];

        foreach ($turnos as $turno) {
            Turno::query()->updateOrCreate(
                ['descripcion' => $turno['descripcion']],
                $turno,
            );
        }
    }
}
