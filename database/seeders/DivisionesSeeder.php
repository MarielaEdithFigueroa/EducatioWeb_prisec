<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionesSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        foreach (['A', 'B', 'C'] as $letra) {
            Division::factory()->create([
                'codigo' => $letra,
                'descripcion' => "División {$letra}",
            ]);
        }
    }
}
