<?php

namespace Database\Seeders;

use App\Models\Aula;
use Illuminate\Database\Seeder;

class AulaSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        foreach ([
            ['descripcion' => 'Aula 1', 'descripcion_larga' => 'Edificio principal, planta baja', 'capacidad' => 30, 'es_especial' => false, 'especialidad' => null],
            ['descripcion' => 'Aula 2', 'descripcion_larga' => 'Edificio viejo, primer piso', 'capacidad' => 25, 'es_especial' => false, 'especialidad' => null],
            ['descripcion' => 'Sala de música', 'descripcion_larga' => 'Edificio principal, planta baja', 'capacidad' => 20, 'es_especial' => true, 'especialidad' => 'Música'],
        ] as $datos) {
            Aula::query()->firstOrCreate(['descripcion' => $datos['descripcion']], $datos);
        }
    }
}
