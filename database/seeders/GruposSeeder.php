<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GruposSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $cursos = Curso::query()->with('nivel')->where('activo', true)
            ->whereHas('nivel', fn (Builder $query): Builder => $query->where('activo', true)
                ->whereIn('codigo', ['INICIAL', 'PRIMARIA', 'SECUNDARIA']))
            ->orderBy('nivel_id')->orderBy('orden')->get();
        $divisiones = Division::query()->where('activo', true)->whereIn('descripcion', ['A', 'B'])->get()->keyBy('descripcion');
        $turnos = Turno::query()->where('activo', true)->whereIn('descripcion', ['Mañana', 'Tarde'])->get()->keyBy('descripcion');
        $planes = PlanEstudio::query()->where('activo', true)->orderBy('orden')->orderBy('id')->get()->groupBy('nivel_id');
        $actual = now()->year;

        DB::transaction(function () use ($cursos, $divisiones, $turnos, $planes, $actual): void {
            foreach ([$actual - 1, $actual, $actual + 1] as $ciclo) {
                foreach ($cursos as $curso) {
                    foreach (['A' => 'Mañana', 'B' => 'Tarde'] as $letra => $horario) {
                        $division = $divisiones->get($letra);
                        $turno = $turnos->get($horario);

                        if ($division === null || $turno === null) {
                            continue;
                        }

                        $grupo = Grupo::query()->firstOrNew([
                            'ciclo_lectivo' => $ciclo,
                            'nivel_id' => $curso->nivel_id,
                            'curso_id' => $curso->id,
                            'division_id' => $division->id,
                            'turno_id' => $turno->id,
                        ]);

                        if ($grupo->exists) {
                            continue;
                        }

                        $grupo->activo = $ciclo >= $actual;
                        $grupo->plan_estudio_id = $planes->get($curso->nivel_id)?->first()?->id;
                        $grupo->setRelation('nivel', $curso->nivel);
                        $grupo->setRelation('curso', $curso);
                        $grupo->setRelation('division', $division);
                        $grupo->setRelation('turno', $turno);
                        $grupo->dh_descripcion = $grupo->descripcion;
                        $grupo->save();
                    }
                }
            }
        });
    }
}
