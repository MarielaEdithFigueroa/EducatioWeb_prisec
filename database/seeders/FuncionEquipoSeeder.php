<?php

namespace Database\Seeders;

use App\Models\FuncionEquipo;
use Illuminate\Database\Seeder;

class FuncionEquipoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $funciones = [
            ['docencia', 'Docencia'],
            ['direccion', 'Dirección'],
            ['secretaria', 'Secretaría'],
            ['preceptoria', 'Preceptoría'],
            ['administracion', 'Administración'],
            ['auxiliares', 'Auxiliares'],
            ['biblioteca', 'Biblioteca'],
            ['orientacion_escolar', 'Orientación escolar'],
            ['maestranza', 'Maestranza'],
            ['mantenimiento', 'Mantenimiento'],
        ];
        foreach ($funciones as $indice => [$codigo, $descripcion]) {
            FuncionEquipo::query()->updateOrCreate(['codigo' => $codigo], ['descripcion' => $descripcion, 'orden' => $indice + 1]);
        }
    }
}
