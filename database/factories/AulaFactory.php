<?php

namespace Database\Factories;

use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Aula> */
class AulaFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'activo' => true,
            'descripcion' => 'Aula '.fake()->unique()->numberBetween(1, 9999),
            'descripcion_larga' => null,
            'capacidad' => 30,
            'es_especial' => false,
            'especialidad' => null,
        ];
    }

    public function especial(): static
    {
        return $this->state(fn (array $attributes): array => [
            'es_especial' => true,
            'especialidad' => 'Música',
        ]);
    }
}
