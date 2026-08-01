<?php

namespace Database\Factories;

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnioLectivo>
 */
class AnioLectivoFactory extends Factory
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
            'anio' => fake()->unique()->numberBetween(2000, 2100),
            'estado' => EstadoAnioLectivo::Preparacion,
        ];
    }

    public function inactivo(): static
    {
        return $this->state(fn (): array => ['activo' => false]);
    }

    public function vigente(): static
    {
        return $this->state(fn (): array => ['estado' => EstadoAnioLectivo::Vigente]);
    }

    public function cerrado(): static
    {
        return $this->state(fn (): array => ['estado' => EstadoAnioLectivo::Cerrado]);
    }
}
