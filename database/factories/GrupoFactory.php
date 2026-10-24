<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Grupo> */
class GrupoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'activo' => true,
            'ciclo_lectivo' => now()->year,
            'nivel_id' => fn (): int => Nivel::query()->firstOrCreate(
                ['codigo' => 'PRIMARIA'], ['descripcion' => 'Primaria', 'activo' => true],
            )->id,
            'curso_id' => fn (array $datos): int => Curso::query()->firstOrCreate(
                ['nivel_id' => $datos['nivel_id'], 'descripcion' => '1.º grado'],
                ['orden' => 1, 'activo' => true],
            )->id,
            'division_id' => fn (): int => Division::query()->create(['descripcion' => fake()->unique()->bothify('D-####??')])->id,
            'turno_id' => fn (): int => Turno::query()->firstOrCreate(['descripcion' => 'Mañana'], ['orden' => 1, 'activo' => true])->id,
            'plan_estudio_id' => null,
        ];
    }
}
