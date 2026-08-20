<?php

namespace Database\Seeders;

use App\Models\Provincia;
use Illuminate\Database\Seeder;

class ProvinciasYCiudadesSeeder extends Seeder
{
    public function run(): void
    {
        $provincias = [
            'CABA' => 'Ciudad Autónoma de Buenos Aires',
            'BA' => 'Buenos Aires',
            'CAT' => 'Catamarca',
            'CBA' => 'Córdoba',
            'CTES' => 'Corrientes',
            'CHA' => 'Chaco',
            'CHU' => 'Chubut',
            'ER' => 'Entre Ríos',
            'FSA' => 'Formosa',
            'JUY' => 'Jujuy',
            'LP' => 'La Pampa',
            'LR' => 'La Rioja',
            'MZA' => 'Mendoza',
            'MIS' => 'Misiones',
            'NQN' => 'Neuquén',
            'RN' => 'Río Negro',
            'SA' => 'Salta',
            'SJ' => 'San Juan',
            'SL' => 'San Luis',
            'SC' => 'Santa Cruz',
            'SF' => 'Santa Fe',
            'SDE' => 'Santiago del Estero',
            'TUC' => 'Tucumán',
            'TDF' => 'Tierra del Fuego, Antártida e Islas del Atlántico Sur',
        ];

        $ciudadesPorProvincia = [
            'CABA' => ['Ciudad Autónoma de Buenos Aires'],
            'BA' => ['La Plata'],
            'CAT' => ['San Fernando del Valle de Catamarca'],
            'CBA' => ['Córdoba'],
            'CTES' => ['Corrientes'],
            'CHA' => ['Resistencia'],
            'CHU' => ['Rawson'],
            'ER' => ['Paraná'],
            'FSA' => ['Formosa'],
            'JUY' => ['San Salvador de Jujuy'],
            'LP' => ['Santa Rosa'],
            'LR' => ['La Rioja'],
            'MZA' => ['Mendoza'],
            'MIS' => ['Posadas'],
            'NQN' => [
                'Neuquén',
                'Plottier',
                'Centenario',
                'Senillosa',
                'Vista Alegre Norte',
                'San Patricio del Chañar',
                'Añelo',
                'Cutral Có',
                'Plaza Huincul',
                'Picún Leufú',
                'Piedra del Águila',
                'Zapala',
                'Las Lajas',
            ],
            'RN' => [
                'Viedma',
                'Cipolletti',
                'General Roca',
                'Cinco Saltos',
                'Allen',
                'General Fernández Oro',
                'Villa Regina',
                'Catriel',
            ],
            'SA' => ['Salta'],
            'SJ' => ['San Juan'],
            'SL' => ['San Luis'],
            'SC' => ['Río Gallegos'],
            'SF' => ['Santa Fe'],
            'SDE' => ['Santiago del Estero'],
            'TUC' => ['San Miguel de Tucumán'],
            'TDF' => ['Ushuaia'],
        ];

        foreach ($provincias as $codigo => $nombre) {
            Provincia::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'activo' => true,
                ],
            );
        }

        foreach ($ciudadesPorProvincia as $codigoProvincia => $ciudades) {
            $provincia = Provincia::query()
                ->where('codigo', $codigoProvincia)
                ->sole();

            foreach ($ciudades as $nombre) {
                $provincia->ciudades()->updateOrCreate(
                    ['nombre' => $nombre],
                    [
                        'activo' => true,
                    ],
                );
            }
        }
    }
}
