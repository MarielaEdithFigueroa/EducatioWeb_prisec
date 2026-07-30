<?php

namespace Database\Factories;

use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grupo>
 */
class GrupoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activo' => true,
            'anio_lectivo_id' => AnioLectivo::factory(),
            'curso_id' => Curso::factory(),
            'division_id' => Division::factory(),
            'turno_id' => Turno::factory(),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
