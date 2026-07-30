<?php

namespace Database\Factories;

use App\Models\AnioLectivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnioLectivo>
 */
class AnioLectivoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activo' => true,
            'anio' => fake()->unique()->numberBetween(1990, 2100),
            'vigente' => false,
        ];
    }

    public function vigente(): static
    {
        return $this->state(fn (array $attributes): array => [
            'vigente' => true,
        ]);
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
