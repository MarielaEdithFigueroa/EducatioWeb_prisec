<?php

namespace Database\Seeders;

use App\Models\MotivoBaja;
use Illuminate\Database\Seeder;

class MotivosBajaSeeder extends Seeder
{
    public function run(): void
    {
        $motivosBaja = [
            'Egreso',
            'Cambio de establecimiento',
            'Mudanza / cambio de domicilio',
            'Motivos económicos',
            'Motivos de salud',
            'Abandono escolar',
            'No renovación de matrícula',
            'Falta de pago',
            'Motivos disciplinarios',
            'Fallecimiento',
            'Otro',
        ];

        foreach ($motivosBaja as $nombre) {
            MotivoBaja::query()->updateOrCreate(
                ['nombre' => $nombre],
                ['activo' => true],
            );
        }
    }
}
