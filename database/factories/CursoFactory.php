<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activo' => true,
            'nivel_id' => Nivel::factory(),
            'codigo' => Str::upper(fake()->unique()->bothify('C##')),
            'descripcion' => fake()->unique()->words(2, true),
            'orden' => fake()->numberBetween(1, 99),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
