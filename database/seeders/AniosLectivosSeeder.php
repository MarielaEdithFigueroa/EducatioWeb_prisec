<?php

namespace Database\Seeders;

use App\Models\AnioLectivo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AniosLectivosSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        AnioLectivo::factory()->create(['anio' => 2025, 'vigente' => false]);
        AnioLectivo::factory()->vigente()->create(['anio' => 2026]);
    }
}
