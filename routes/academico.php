<?php

use App\Http\Controllers\Academico\AnioLectivoController;
use App\Http\Controllers\Academico\CursoController;
use App\Http\Controllers\Academico\DivisionController;
use App\Http\Controllers\Academico\GrupoController;
use App\Http\Controllers\Academico\NivelController;
use App\Http\Controllers\Academico\TurnoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('academico')->name('academico.')->group(function () {
    Route::resource('niveles', NivelController::class)
        ->parameters(['niveles' => 'nivel'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('niveles/{nivel}/desactivar', [NivelController::class, 'desactivar'])->name('niveles.desactivar');
    Route::patch('niveles/{nivel}/reactivar', [NivelController::class, 'reactivar'])->name('niveles.reactivar');

    Route::resource('turnos', TurnoController::class)
        ->parameters(['turnos' => 'turno'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('turnos/{turno}/desactivar', [TurnoController::class, 'desactivar'])->name('turnos.desactivar');
    Route::patch('turnos/{turno}/reactivar', [TurnoController::class, 'reactivar'])->name('turnos.reactivar');

    Route::resource('divisiones', DivisionController::class)
        ->parameters(['divisiones' => 'division'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('divisiones/{division}/desactivar', [DivisionController::class, 'desactivar'])->name('divisiones.desactivar');
    Route::patch('divisiones/{division}/reactivar', [DivisionController::class, 'reactivar'])->name('divisiones.reactivar');

    Route::resource('cursos', CursoController::class)
        ->parameters(['cursos' => 'curso'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('cursos/{curso}/desactivar', [CursoController::class, 'desactivar'])->name('cursos.desactivar');
    Route::patch('cursos/{curso}/reactivar', [CursoController::class, 'reactivar'])->name('cursos.reactivar');

    Route::resource('anios-lectivos', AnioLectivoController::class)
        ->parameters(['anios-lectivos' => 'anioLectivo'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('anios-lectivos/{anioLectivo}/desactivar', [AnioLectivoController::class, 'desactivar'])->name('anios-lectivos.desactivar');
    Route::patch('anios-lectivos/{anioLectivo}/reactivar', [AnioLectivoController::class, 'reactivar'])->name('anios-lectivos.reactivar');

    Route::resource('grupos', GrupoController::class)
        ->parameters(['grupos' => 'grupo'])
        ->only(['index', 'create', 'store', 'edit', 'update']);
    Route::patch('grupos/{grupo}/desactivar', [GrupoController::class, 'desactivar'])->name('grupos.desactivar');
    Route::patch('grupos/{grupo}/reactivar', [GrupoController::class, 'reactivar'])->name('grupos.reactivar');
});
