<?php

namespace Database\Factories;

use App\Models\Integrante;
use App\Models\ReferenciaLaboralIntegrante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReferenciaLaboralIntegrante>
 */
class ReferenciaLaboralIntegranteFactory extends Factory
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
            'integrante_id' => Integrante::factory(),
            'lugar' => fake()->company(),
        ];
    }
}
