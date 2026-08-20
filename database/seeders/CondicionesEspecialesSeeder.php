<?php

namespace Database\Seeders;

use App\Models\CondicionEspecial;
use Illuminate\Database\Seeder;

class CondicionesEspecialesSeeder extends Seeder
{
    /**
     * Catálogo `tipo_condicionespecial` tomado de la base del legacy
     * y confirmado mediante capturas provistas por Daniel.
     */
    public function run(): void
    {
        $condicionesEspeciales = [
            1 => 'TEA (Trastorno del Espectro Autista)',
            2 => 'TDAH (Trastorno por Déficit de Atención e Hiperactividad)',
            3 => 'Dislexia',
            4 => 'Discalculia',
            5 => 'Disgrafía',
            6 => 'Trastorno específico del lenguaje (TEL/TDL)',
            7 => 'Dificultades de aprendizaje',
            8 => 'Discapacidad intelectual',
            9 => 'Discapacidad motora',
            10 => 'Discapacidad visual',
            11 => 'Discapacidad auditiva',
            12 => 'Trastornos del habla o la comunicación',
            13 => 'Altas capacidades / superdotación',
            14 => 'Condición emocional o de salud mental que pueda requerir acompañamiento particular',
            15 => 'Enfermedad crónica que pueda afectar su asistencia o proceso de aprendizaje',
            16 => 'Alergias u otras condiciones de salud relevantes para su permanencia en la institución',
        ];

        foreach ($condicionesEspeciales as $id => $descripcion) {
            CondicionEspecial::query()->updateOrCreate(
                ['id' => $id],
                ['descripcion' => $descripcion, 'activo' => true],
            );
        }
    }
}
