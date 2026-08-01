<?php

namespace Database\Factories;

use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nivel = null;
        $resolverNivel = function () use (&$nivel): Nivel {
            return $nivel ??= Nivel::factory()->create();
        };

        return [
            'activo' => true,
            'anio_lectivo_id' => AnioLectivo::factory(),
            'plan_estudio_id' => fn (): int => PlanEstudio::factory()->for($resolverNivel())->create()->id,
            'curso_id' => fn (): int => Curso::factory()->for($resolverNivel())->create()->id,
            'division_id' => fn (): int => Division::factory()->for($resolverNivel())->create()->id,
            'turno_id' => fn (): int => Turno::factory()->for($resolverNivel())->create()->id,
        ];
    }

    public function paraNivel(Nivel $nivel): static
    {
        return $this->state(fn (): array => [
            'plan_estudio_id' => PlanEstudio::factory()->for($nivel),
            'curso_id' => Curso::factory()->for($nivel),
            'division_id' => Division::factory()->for($nivel),
            'turno_id' => Turno::factory()->for($nivel),
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
