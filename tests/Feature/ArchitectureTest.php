<?php

use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Support\Facades\Schema;

it('keeps activo immediately after id', function () {
    $tables = collect(Schema::getTables())
        ->pluck('name')
        ->filter(fn (string $table) => in_array('activo', Schema::getColumnListing($table)));

    foreach ($tables as $table) {
        expect(array_search('activo', Schema::getColumnListing($table)))
            ->toBe(1, "En '$table', activo debe ser la segunda columna.");
    }
});

it('uses conventional identifiers in academic tables', function () {
    $expectedIdentifiers = [
        'niveles' => ['id'],
        'cursos' => ['id', 'nivel_id'],
        'divisiones' => ['id'],
        'turnos' => ['id'],
        'planes_estudio' => ['id', 'nivel_id'],
        'grupos' => ['id', 'curso_id', 'turno_id', 'division_id', 'plan_estudio_id', 'nivel_id'],
    ];

    foreach ($expectedIdentifiers as $table => $columns) {
        expect(Schema::hasColumns($table, $columns))
            ->toBeTrue("La tabla '$table' debe usar identificadores convencionales de Laravel.");
    }

    $legacyIdentifiers = [
        'niveles' => ['id_nivel'],
        'cursos' => ['id_curso', 'id_nivel'],
        'divisiones' => ['id_division'],
        'turnos' => ['id_turno'],
        'planes_estudio' => ['id_plan_estudio', 'id_nivel'],
        'grupos' => ['id_grupo', 'id_curso', 'id_turno', 'id_division', 'id_plan_estudio', 'id_nivel'],
    ];

    foreach ($legacyIdentifiers as $table => $columns) {
        foreach ($columns as $column) {
            expect(Schema::hasColumn($table, $column))
                ->toBeFalse("La tabla '$table' no debe conservar la columna '$column'.");
        }
    }
});

it('keeps domain tables free of eloquent timestamps', function () {
    $domainModels = [
        'niveles' => Nivel::class,
        'cursos' => Curso::class,
        'divisiones' => Division::class,
        'turnos' => Turno::class,
        'planes_estudio' => PlanEstudio::class,
        'grupos' => Grupo::class,
    ];

    foreach ($domainModels as $table => $modelClass) {
        expect(Schema::hasColumn($table, 'created_at'))
            ->toBeFalse("La tabla de dominio '$table' no debe tener created_at.")
            ->and(Schema::hasColumn($table, 'updated_at'))
            ->toBeFalse("La tabla de dominio '$table' no debe tener updated_at.")
            ->and((new $modelClass)->usesTimestamps())
            ->toBeFalse("El modelo '$modelClass' no debe administrar timestamps.");
    }
});
