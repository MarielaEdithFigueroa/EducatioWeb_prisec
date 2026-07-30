<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Nivel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CursosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $ordinales = ['1er', '2do', '3er', '4to', '5to', '6to'];

        $planes = [
            'PRI' => 'Grado',
            'SEC' => 'Año',
        ];

        foreach ($planes as $codigoNivel => $sustantivo) {
            $nivel = Nivel::where('codigo', $codigoNivel)->first();

            if (! $nivel) {
                continue;
            }

            foreach ($ordinales as $i => $ordinal) {
                $orden = $i + 1;

                Curso::factory()->create([
                    'nivel_id' => $nivel->id,
                    'codigo' => "{$codigoNivel}-{$orden}",
                    'descripcion' => "{$ordinal} {$sustantivo}",
                    'orden' => $orden,
                ]);
            }
        }
    }
}
