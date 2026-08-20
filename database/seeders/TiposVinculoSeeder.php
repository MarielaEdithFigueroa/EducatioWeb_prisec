<?php

namespace Database\Seeders;

use App\Models\TipoVinculo;
use Illuminate\Database\Seeder;

class TiposVinculoSeeder extends Seeder
{
    /**
     * Basado en el catálogo `Tipo-Responsable` del legacy, ajustado con
     * Daniel. IDs explícitos y consecutivos para que sean estables y
     * predecibles: es un enumerado que solo va a crecer, nunca a
     * achicarse (sin `activo`).
     */
    public function run(): void
    {
        $tiposVinculo = [
            1 => 'Padre',
            2 => 'Madre',
            3 => 'Tutor/a',
            4 => 'Nuevo Papá',
            5 => 'Nueva Mamá',
            8 => 'Abuelo',
            9 => 'Abuela',
            6 => 'Hermano/a',
            10 => 'Tío/a',
            12 => 'Pareja',
            13 => 'Madrina / Padrino',
            14 => 'Self',
            15 => 'Otro parentesco',
            16 => 'Sin Determinar',
        ];

        foreach ($tiposVinculo as $id => $nombre) {
            TipoVinculo::query()->updateOrCreate(
                ['id' => $id],
                ['nombre' => $nombre],
            );
        }
    }
}
