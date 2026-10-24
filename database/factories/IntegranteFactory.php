<?php

namespace Database\Factories;

use App\Models\Integrante;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Integrante> */
class IntegranteFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'activo' => true,
            'legajo' => (string) fake()->unique()->numberBetween(1000, 999999),
            'apellido' => fake()->lastName(),
            'nombre' => fake()->firstName(),
            'fecha_ingreso' => '2020-03-01',
            'tiene_aportes' => false,
        ];
    }
}
