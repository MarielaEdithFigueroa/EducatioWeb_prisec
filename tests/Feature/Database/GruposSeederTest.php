<?php

use App\Models\Grupo;
use App\Models\Nivel;
use Database\Seeders\CursosSeeder;
use Database\Seeders\DivisionesSeeder;
use Database\Seeders\GruposSeeder;
use Database\Seeders\NivelesSeeder;
use Database\Seeders\PlanesEstudioSeeder;
use Database\Seeders\TurnosSeeder;

it('incluye grupos coherentes de tres ciclos en el seed general con eventos silenciados', function () {
    $this->travelTo(now()->setDate(2026, 10, 24));
    $this->seed();

    $grupos = Grupo::query()->with(['nivel', 'curso', 'division', 'turno', 'planEstudio'])->get();
    expect($grupos)->toHaveCount(90)
        ->and($grupos->pluck('ciclo_lectivo')->unique()->sort()->values()->all())->toBe([2025, 2026, 2027])
        ->and($grupos->where('activo', false))->toHaveCount(30);

    foreach ($grupos as $grupo) {
        expect($grupo->curso->nivel_id)->toBe($grupo->nivel_id)
            ->and($grupo->planEstudio->nivel_id)->toBe($grupo->nivel_id)
            ->and($grupo->dh_descripcion)->toBe($grupo->descripcion)
            ->and($grupo->turno->descripcion)->toBe($grupo->division->descripcion === 'A' ? 'Mañana' : 'Tarde');
    }
});

it('puede repetirse sin duplicar grupos ni cambiar los editados o desactivados', function () {
    $this->seed([NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class, PlanesEstudioSeeder::class, GruposSeeder::class]);
    $grupo = Grupo::query()->where('ciclo_lectivo', now()->year)->firstOrFail();
    $grupo->update(['activo' => false, 'plan_estudio_id' => null]);
    $antes = Grupo::query()->orderBy('id')->get()->toArray();

    $this->seed(GruposSeeder::class);

    expect(Grupo::query()->orderBy('id')->get()->toArray())->toBe($antes);
});

it('omite niveles inactivos y permite grupos sin plan', function () {
    $this->seed([NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class]);
    Nivel::query()->where('codigo', 'INICIAL')->update(['activo' => false]);
    $this->seed(GruposSeeder::class);

    expect(Grupo::query()->count())->toBe(72)
        ->and(Grupo::query()->whereNotNull('plan_estudio_id')->exists())->toBeFalse()
        ->and(Grupo::query()->whereHas('nivel', fn ($query) => $query->where('codigo', 'INICIAL'))->exists())->toBeFalse();
});

it('no carga ejemplos fuera de desarrollo y tests', function () {
    $this->seed([NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class]);
    $entorno = app()->environment();
    app()->instance('env', 'production');

    try {
        app(GruposSeeder::class)->run();
        expect(Grupo::query()->count())->toBe(0);
    } finally {
        app()->instance('env', $entorno);
    }
});
