<?php

namespace Database\Seeders;

use App\Models\Turno;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TurnosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $turnos = [
            ['codigo' => 'MAN', 'descripcion' => 'Mañana'],
            ['codigo' => 'TAR', 'descripcion' => 'Tarde'],
            ['codigo' => 'UNI', 'descripcion' => 'Única'],
        ];

        foreach ($turnos as $turno) {
            Turno::factory()->create($turno);
        }
    }
}
