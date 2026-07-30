<?php

namespace Database\Factories;

use App\Models\Nivel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Nivel>
 */
class NivelFactory extends Factory
{
    public function definition(): array
    {
        return [
            'activo' => true,
            'codigo' => Str::upper(fake()->unique()->bothify('N##')),
            'descripcion' => fake()->unique()->words(2, true),
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'activo' => false,
        ]);
    }
}
