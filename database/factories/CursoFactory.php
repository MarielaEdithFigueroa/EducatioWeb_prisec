<?php

namespace Database\Factories;

use App\Models\Curso;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Curso>
 */
class CursoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'activo' => true,
            'nivel_id' => Nivel::factory(),
            'descripcion' => fake()->unique()->numerify('Curso ##'),
            'orden' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
