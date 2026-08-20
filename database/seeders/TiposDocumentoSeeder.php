<?php

namespace Database\Seeders;

use App\Models\TipoDocumento;
use Illuminate\Database\Seeder;

class TiposDocumentoSeeder extends Seeder
{
    /**
     * IDs explícitos y consecutivos: es un enumerado que solo va a crecer,
     * nunca a achicarse (sin `activo`, ver .ai/guidelines core.md).
     */
    public function run(): void
    {
        $tiposDocumento = [
            1 => 'DNI',
            2 => 'Libreta Cívica',
            3 => 'Libreta de Enrolamiento',
            4 => 'Pasaporte',
            5 => 'Cédula de Identidad',
            6 => 'Otro',
        ];

        foreach ($tiposDocumento as $id => $nombre) {
            TipoDocumento::query()->updateOrCreate(
                ['id' => $id],
                ['nombre' => $nombre],
            );
        }
    }
}
