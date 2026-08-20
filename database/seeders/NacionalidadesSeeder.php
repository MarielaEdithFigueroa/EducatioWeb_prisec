<?php

namespace Database\Seeders;

use App\Models\Nacionalidad;
use Illuminate\Database\Seeder;

class NacionalidadesSeeder extends Seeder
{
    /**
     * Nacionalidades cargadas como gentilicio (concuerda con "nacionalidad: ___"),
     * no como nombre de país. Orden: Argentina, limítrofes, resto de Sudamérica,
     * los países más relevantes de Europa, y por último USA y Japón.
     * Código: ISO 3166-1 alpha-3, un estándar reconocido internacionalmente.
     */
    public function run(): void
    {
        $nacionalidades = [
            // Argentina
            'ARG' => 'Argentina',

            // Limítrofes
            'BOL' => 'Boliviana',
            'BRA' => 'Brasileña',
            'CHL' => 'Chilena',
            'PRY' => 'Paraguaya',
            'URY' => 'Uruguaya',

            // Resto de Sudamérica
            'COL' => 'Colombiana',
            'VEN' => 'Venezolana',
            'PER' => 'Peruana',
            'ECU' => 'Ecuatoriana',

            // Europa (los más relevantes)
            'ESP' => 'Española',
            'ITA' => 'Italiana',
            'FRA' => 'Francesa',
            'DEU' => 'Alemana',
            'GBR' => 'Británica',
            'PRT' => 'Portuguesa',
            'NLD' => 'Holandesa',
            'CHE' => 'Suiza',
            'RUS' => 'Rusa',
            'UKR' => 'Ucraniana',
            'POL' => 'Polaca',
            'GRC' => 'Griega',
            'SWE' => 'Sueca',

            // USA
            'USA' => 'Estadounidense',

            // Japón
            'JPN' => 'Japonesa',
        ];

        foreach ($nacionalidades as $codigo => $nombre) {
            Nacionalidad::query()->updateOrCreate(
                ['codigo' => $codigo],
                [
                    'nombre' => $nombre,
                    'activo' => true,
                ],
            );
        }
    }
}
