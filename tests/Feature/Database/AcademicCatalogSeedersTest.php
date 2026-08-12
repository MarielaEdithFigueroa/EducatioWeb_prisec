<?php

use App\Models\Curso;
use App\Models\Division;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Database\Seeders\CursosSeeder;
use Database\Seeders\DivisionesSeeder;
use Database\Seeders\NivelesSeeder;
use Database\Seeders\PlanesEstudioSeeder;
use Database\Seeders\TurnosSeeder;
use Illuminate\Support\Str;

it('seeds a small school academic catalog idempotently', function () {
    $this->seed();

    $turnoIds = Turno::query()->orderBy('id')->pluck('id')->all();
    $divisionIds = Division::query()->orderBy('id')->pluck('id')->all();
    $planIds = PlanEstudio::query()->orderBy('id')->pluck('id')->all();

    $this->seed([
        TurnosSeeder::class,
        DivisionesSeeder::class,
        PlanesEstudioSeeder::class,
    ]);

    expect(Turno::query()->orderBy('orden')->get()->map(
        fn (Turno $turno) => [$turno->descripcion, $turno->activo, $turno->orden],
    )->all())->toBe([
        ['Mañana', true, 1],
        ['Tarde', true, 2],
    ])->and(Turno::query()->orderBy('id')->pluck('id')->all())->toBe($turnoIds);

    expect(Division::query()->orderBy('orden')->get()->map(
        fn (Division $division) => [$division->descripcion, $division->activo, $division->orden],
    )->all())->toBe([
        ['A', true, 1],
        ['B', true, 2],
    ])->and(Division::query()->orderBy('id')->pluck('id')->all())->toBe($divisionIds);

    $planesEsperados = Nivel::query()
        ->orderBy('id')
        ->get()
        ->map(fn (Nivel $nivel) => [
            'Plan de estudios '.Str::lower($nivel->descripcion),
            true,
            1,
            $nivel->codigo,
        ])
        ->all();

    expect(PlanEstudio::query()->with('nivel')->orderBy('nivel_id')->get()->map(
        fn (PlanEstudio $planEstudio) => [
            $planEstudio->descripcion,
            $planEstudio->activo,
            $planEstudio->orden,
            $planEstudio->nivel->codigo,
        ],
    )->all())->toBe($planesEsperados)
        ->and(PlanEstudio::query()->orderBy('id')->pluck('id')->all())->toBe($planIds);
});

it('siembra los cursos por nivel de forma idempotente', function () {
    $this->seed([
        NivelesSeeder::class,
        CursosSeeder::class,
    ]);

    $idsCursos = Curso::query()->orderBy('id')->pluck('id')->all();

    $this->seed(CursosSeeder::class);

    $cursosEsperados = [
        'INICIAL' => ['Sala de 3', 'Sala de 4', 'Sala de 5'],
        'PRIMARIA' => ['1.º grado', '2.º grado', '3.º grado', '4.º grado', '5.º grado', '6.º grado', '7.º grado'],
        'SECUNDARIA' => ['1.º año', '2.º año', '3.º año', '4.º año', '5.º año'],
    ];

    expect(Curso::query()->count())->toBe(15)
        ->and(Curso::query()->orderBy('id')->pluck('id')->all())->toBe($idsCursos);

    foreach ($cursosEsperados as $codigoNivel => $descripciones) {
        $nivel = Nivel::query()
            ->where('codigo', $codigoNivel)
            ->sole();

        $cursos = Curso::query()
            ->whereBelongsTo($nivel)
            ->orderBy('orden')
            ->get();

        expect($cursos->pluck('descripcion')->all())->toBe($descripciones)
            ->and($cursos->pluck('orden')->all())->toBe(range(1, count($descripciones)))
            ->and($cursos->every(fn (Curso $curso) => $curso->activo))->toBeTrue();
    }
});
