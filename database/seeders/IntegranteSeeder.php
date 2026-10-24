<?php

namespace Database\Seeders;

use App\Models\FuncionEquipo;
use App\Models\Integrante;
use Illuminate\Database\Seeder;

class IntegranteSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }
        $this->call(FuncionEquipoSeeder::class);
        $integrante = Integrante::query()->firstOrCreate(['legajo' => '1'], [
            'apellido' => 'García', 'nombre' => 'María', 'fecha_ingreso' => '2020-03-01',
            'titulo_habilitante' => 'Profesora de Educación Primaria',
        ]);
        $integrante->asignacionesFunciones()->firstOrCreate(['funcion_equipo_id' => FuncionEquipo::query()->where('codigo', 'docencia')->sole()->id]);
    }
}
