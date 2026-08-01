<?php

namespace Database\Factories;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Nivel>
 */
class NivelFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('NIV-##'),
            'descripcion' => fake()->unique()->words(2, true),
            'orden' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
