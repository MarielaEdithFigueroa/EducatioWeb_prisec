<?php

namespace Database\Seeders;

use App\Models\Nivel;
use App\Models\PlanEstudio;
use Illuminate\Database\Seeder;

class PlanesEstudioSeeder extends Seeder
{
    public function run(): void
    {
        $nivelSecundario = Nivel::query()
            ->where('codigo', 'SECUNDARIA')
            ->sole();

        PlanEstudio::query()->updateOrCreate(
            [
                'nivel_id' => $nivelSecundario->id,
                'descripcion' => 'Plan de estudios secundario',
            ],
            [
                'activo' => true,
                'orden' => 1,
            ],
        );
    }
}
