<?php

use App\Models\Division;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Database\Seeders\DivisionesSeeder;
use Database\Seeders\PlanesEstudioSeeder;
use Database\Seeders\TurnosSeeder;

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

    $planEstudio = PlanEstudio::query()->with('nivel')->sole();

    expect($planEstudio->descripcion)->toBe('Plan de estudios secundario')
        ->and($planEstudio->activo)->toBeTrue()
        ->and($planEstudio->orden)->toBe(1)
        ->and($planEstudio->nivel->codigo)->toBe('SECUNDARIA')
        ->and(PlanEstudio::query()->orderBy('id')->pluck('id')->all())->toBe($planIds);
});
