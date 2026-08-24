<?php

use App\Models\Ciudad;
use App\Models\Provincia;
use Database\Seeders\ProvinciasYCiudadesSeeder;

it('siembra provincias y ciudades de forma idempotente', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);

    $idsProvincias = Provincia::query()->orderBy('id')->pluck('id')->all();
    $idsCiudades = Ciudad::query()->orderBy('id')->pluck('id')->all();

    $this->seed(ProvinciasYCiudadesSeeder::class);

    $neuquen = Provincia::query()->where('codigo', 'NQN')->sole();
    $rioNegro = Provincia::query()->where('codigo', 'RN')->sole();
    $exterior = Provincia::query()->where('codigo', 'EXT')->sole();

    expect(Provincia::query()->count())->toBe(25)
        ->and(Ciudad::query()->count())->toBe(44)
        ->and(Provincia::query()->orderBy('id')->pluck('id')->all())->toBe($idsProvincias)
        ->and(Ciudad::query()->orderBy('id')->pluck('id')->all())->toBe($idsCiudades)
        ->and(Provincia::query()->where('codigo', 'CABA')->value('nombre'))
        ->toBe('Ciudad Autónoma de Buenos Aires')
        ->and(Provincia::query()->where('codigo', 'TDF')->value('nombre'))
        ->toBe('Tierra del Fuego, Antártida e Islas del Atlántico Sur')
        ->and($neuquen->ciudades()->count())->toBe(13)
        ->and($neuquen->ciudades()->where('nombre', 'Neuquén')->exists())->toBeTrue()
        ->and($neuquen->ciudades()->where('nombre', 'Covunco Centro')->exists())->toBeFalse()
        ->and($rioNegro->ciudades()->count())->toBe(8)
        ->and($exterior->nombre)->toBe('Otro país')
        ->and($exterior->activo)->toBeTrue()
        ->and($exterior->ciudades()->count())->toBe(1)
        ->and($exterior->ciudades()->where('nombre', 'Otra localidad del exterior')->where('activo', true)->exists())->toBeTrue()
        ->and($rioNegro->ciudades()->where('nombre', 'Viedma')->exists())->toBeTrue()
        ->and($rioNegro->ciudades()->where('nombre', 'Barrio Pino Azul')->exists())->toBeFalse();
});
