<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Turno;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GruposSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $anioLectivo = AnioLectivo::where('vigente', true)->first();
        $division = Division::where('codigo', 'A')->first();
        $turno = Turno::where('codigo', 'UNI')->first();

        if (! $anioLectivo || ! $division || ! $turno) {
            return;
        }

        Curso::orderBy('nivel_id')->orderBy('orden')->each(function (Curso $curso) use ($anioLectivo, $division, $turno) {
            Grupo::factory()->create([
                'anio_lectivo_id' => $anioLectivo->id,
                'curso_id' => $curso->id,
                'division_id' => $division->id,
                'turno_id' => $turno->id,
            ]);
        });
    }
}
