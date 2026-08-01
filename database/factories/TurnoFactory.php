<?php

namespace Database\Factories;

use App\Models\Nivel;
use App\Models\Turno;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Turno>
 */
class TurnoFactory extends Factory
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
            'descripcion' => fake()->randomElement(['Mañana', 'Tarde', 'Noche']).' '.fake()->unique()->numerify('##'),
            'orden' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
