<?php

namespace Database\Seeders;

use App\Models\Nivel;
use Illuminate\Database\Seeder;

class CursosSeeder extends Seeder
{
    public function run(): void
    {
        $cursosPorNivel = [
            'INICIAL' => [
                ['descripcion' => 'Sala de 3', 'orden' => 1],
                ['descripcion' => 'Sala de 4', 'orden' => 2],
                ['descripcion' => 'Sala de 5', 'orden' => 3],
            ],
            'PRIMARIA' => [
                ['descripcion' => '1.º grado', 'orden' => 1],
                ['descripcion' => '2.º grado', 'orden' => 2],
                ['descripcion' => '3.º grado', 'orden' => 3],
                ['descripcion' => '4.º grado', 'orden' => 4],
                ['descripcion' => '5.º grado', 'orden' => 5],
                ['descripcion' => '6.º grado', 'orden' => 6],
                ['descripcion' => '7.º grado', 'orden' => 7],
            ],
            'SECUNDARIA' => [
                ['descripcion' => '1.º año', 'orden' => 1],
                ['descripcion' => '2.º año', 'orden' => 2],
                ['descripcion' => '3.º año', 'orden' => 3],
                ['descripcion' => '4.º año', 'orden' => 4],
                ['descripcion' => '5.º año', 'orden' => 5],
            ],
        ];

        foreach ($cursosPorNivel as $codigoNivel => $cursos) {
            $nivel = Nivel::query()
                ->where('codigo', $codigoNivel)
                ->sole();

            foreach ($cursos as $curso) {
                $nivel->cursos()->updateOrCreate(
                    ['descripcion' => $curso['descripcion']],
                    [
                        'activo' => true,
                        'orden' => $curso['orden'],
                    ],
                );
            }
        }
    }
}
