<?php

namespace Database\Factories;

use App\Models\Log;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Log> */
class LogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'entidad' => fake()->word(),
            'entidad_id' => fake()->numberBetween(1, 1000),
            'accion' => fake()->randomElement(['create', 'update', 'delete']),
            'usuario_id' => null,
            'login' => fake()->userName(),
            'session_id' => fake()->uuid(),
            'anterior' => null,
            'nuevo' => ['activo' => true],
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'origen' => 'web',
        ];
    }
}
