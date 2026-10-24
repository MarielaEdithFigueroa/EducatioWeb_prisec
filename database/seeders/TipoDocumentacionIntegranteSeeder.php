<?php

namespace Database\Seeders;

use App\Models\TipoDocumentacionIntegrante;
use Illuminate\Database\Seeder;

class TipoDocumentacionIntegranteSeeder extends Seeder
{
    public function run(): void
    {
        $descripciones = [
            'Copia de DNI',
            'Constancia de CUIL',
            'Alta AFIP',
            'Nota SUAF',
            'Nota de obra social',
            'Declaración de domicilio',
            'Planilla de alta',
            'Curriculum vitae',
            'Formulario AFIP 572',
        ];
        foreach ($descripciones as $descripcion) {
            TipoDocumentacionIntegrante::query()->firstOrCreate(['descripcion' => $descripcion]);
        }
    }
}
