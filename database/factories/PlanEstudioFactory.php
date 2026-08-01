<?php

namespace Database\Factories;

use App\Models\Nivel;
use App\Models\PlanEstudio;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlanEstudio>
 */
class PlanEstudioFactory extends Factory
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
            'codigo' => fake()->unique()->bothify('PLAN-###'),
            'descripcion' => fake()->unique()->words(3, true),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }
}
