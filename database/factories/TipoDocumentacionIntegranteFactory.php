<?php

namespace Database\Factories;

use App\Models\TipoDocumentacionIntegrante;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TipoDocumentacionIntegrante> */
class TipoDocumentacionIntegranteFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'activo' => true,
            'descripcion' => 'Tipo de documentación '.fake()->unique()->numerify('#####'),
        ];
    }
}
