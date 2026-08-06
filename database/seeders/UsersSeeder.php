<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $password = env('SEED_ADMIN_PASSWORD');

        if (! is_string($password) || $password === '') {
            throw new \RuntimeException('Definí SEED_ADMIN_PASSWORD antes de ejecutar DatabaseSeeder.');
        }

        $login = env('SEED_ADMIN_LOGIN', 'admin');

        User::query()->updateOrCreate([
            'login' => $login,
        ], [
            'nombre' => env('SEED_ADMIN_NOMBRE', 'Administrador'),
            'apellido' => env('SEED_ADMIN_APELLIDO', 'Local'),
            'password' => $password,
        ]);
    }
}
