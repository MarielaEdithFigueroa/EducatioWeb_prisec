<?php

namespace Database\Seeders;

use App\Models\Nivel;
use App\Models\PlanEstudio;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanesEstudioSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Nivel::query()->orderBy('id')->get() as $nivel) {
            PlanEstudio::query()->updateOrCreate(
                ['nivel_id' => $nivel->id],
                [
                    'descripcion' => 'Plan de estudios '.Str::lower($nivel->descripcion),
                    'activo' => true,
                    'orden' => 1,
                ],
            );
        }
    }
}
