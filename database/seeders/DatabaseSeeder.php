<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ProvinciasYCiudadesSeeder::class,
            NacionalidadesSeeder::class,
            GruposSanguineosSeeder::class,
            MotivosBajaSeeder::class,
            TiposDocumentoSeeder::class,
            TiposVinculoSeeder::class,
            CondicionesEspecialesSeeder::class,
            AlumnosSeeder::class,
            NivelesSeeder::class,
            CursosSeeder::class,
            TurnosSeeder::class,
            DivisionesSeeder::class,
            PlanesEstudioSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
