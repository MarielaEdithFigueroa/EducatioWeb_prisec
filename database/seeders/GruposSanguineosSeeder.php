<?php

namespace Database\Seeders;

use App\Models\GrupoSanguineo;
use Illuminate\Database\Seeder;

class GruposSanguineosSeeder extends Seeder
{
    public function run(): void
    {
        $gruposSanguineos = [
            'A+' => 'A Positivo',
            'A-' => 'A Negativo',
            'B+' => 'B Positivo',
            'B-' => 'B Negativo',
            'AB+' => 'AB Positivo',
            'AB-' => 'AB Negativo',
            'O+' => 'O Positivo',
            'O-' => 'O Negativo',
        ];

        foreach ($gruposSanguineos as $codigo => $nombre) {
            GrupoSanguineo::query()->updateOrCreate(
                ['codigo' => $codigo],
                ['nombre' => $nombre],
            );
        }
    }
}
