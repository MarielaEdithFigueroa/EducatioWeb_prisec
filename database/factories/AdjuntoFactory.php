<?php

namespace Database\Factories;

use App\Models\Adjunto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Adjunto> */
class AdjuntoFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'activo' => true,
            'nombre_original' => 'documento.pdf',
            'disco' => 'adjuntos',
            'ruta' => 'archivos/'.fake()->uuid().'.pdf',
            'mime' => 'application/pdf',
            'tamano_bytes' => 100,
            'observaciones' => null,
            'fecha_registro' => now(),
            'usuario_id' => User::factory(),
        ];
    }
}
