<?php

namespace Database\Factories;

use App\Models\Division;
use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Division>
 */
class DivisionFactory extends Factory
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
            'descripcion' => fake()->randomElement(['A', 'B', 'C', 'D']).fake()->unique()->numerify('##'),
            'orden' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
