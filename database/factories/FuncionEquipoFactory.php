<?php

namespace Database\Factories;

use App\Models\FuncionEquipo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuncionEquipo>
 */
class FuncionEquipoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'codigo' => fake()->unique()->lexify('funcion_????????'),
            'descripcion' => fake()->unique()->sentence(3),
            'orden' => 1,
        ];
    }
}
