<?php

namespace Database\Factories;

use App\Models\ContactoIntegrante;
use App\Models\Integrante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactoIntegrante>
 */
class ContactoIntegranteFactory extends Factory
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
            'nombre' => fake()->name(),
            'vinculo' => 'Familiar',
        ];
    }
}
