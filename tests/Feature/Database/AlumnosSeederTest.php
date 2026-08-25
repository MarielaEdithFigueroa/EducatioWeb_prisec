<?php

use App\Models\Alumno;
use App\Rules\Cuit;
use Database\Seeders\AlumnosSeeder;
use Illuminate\Support\Facades\Validator;

it('siembra alumnos de ejemplo de forma coherente e idempotente', function () {
    $this->seed();

    $alumnos = Alumno::query()
        ->orderBy('id')
        ->get();
    $ids = $alumnos->pluck('id')->all();

    $this->seed(AlumnosSeeder::class);

    expect($alumnos)->toHaveCount(12)
        ->and(Alumno::query()->orderBy('id')->pluck('id')->all())->toBe($ids)
        ->and(Alumno::query()->where('activo', true)->count())->toBe(11)
        ->and(Alumno::query()->where('activo', false)->count())->toBe(1)
        ->and($alumnos->pluck('legajo')->unique())->toHaveCount(12)
        ->and($alumnos->pluck('numero_documento')->unique())->toHaveCount(12)
        ->and($alumnos->pluck('cuilt')->unique())->toHaveCount(12);

    $sofia = Alumno::query()
        ->with(['ciudad.provincia', 'ciudadNacimiento', 'nacionalidad', 'grupoSanguineo', 'tipoDocumento'])
        ->where('legajo', 'PRI-2026-0001')
        ->sole();

    expect($sofia->apellido)->toBe('Acuña')
        ->and($sofia->tipoDocumento->nombre)->toBe('DNI')
        ->and($sofia->ciudad->nombre)->toBe('Neuquén')
        ->and($sofia->ciudad->provincia->codigo)->toBe('NQN')
        ->and($sofia->ciudadNacimiento->nombre)->toBe('Neuquén')
        ->and($sofia->nacionalidad->codigo)->toBe('ARG')
        ->and($sofia->grupoSanguineo->codigo)->toBe('O+');

    $sam = Alumno::query()
        ->where('legajo', 'SEC-2026-0012')
        ->sole();

    expect($sam->nombre_elegido)->toBe('Sam')
        ->and($sam->sexo_registral)->toBe('X')
        ->and($sam->genero)->toBe('Otra identidad')
        ->and($sam->genero_autodescripcion)->toBe('Género fluido');

    $alumnaInactiva = Alumno::query()
        ->with('motivoBaja')
        ->where('activo', false)
        ->sole();

    expect($alumnaInactiva->legajo)->toBe('SEC-2026-0010')
        ->and($alumnaInactiva->fecha_baja?->format('Y-m-d'))->toBe('2026-07-10')
        ->and($alumnaInactiva->motivoBaja->nombre)->toBe('Cambio de establecimiento');

    foreach ($alumnos as $alumno) {
        expect(Validator::make(
            ['cuilt' => $alumno->cuilt],
            ['cuilt' => [new Cuit]],
        )->passes())->toBeTrue();
    }
});
