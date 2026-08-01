<?php

use App\Http\Controllers\Academico\AnioLectivoController;
use App\Http\Controllers\Academico\CursoController;
use App\Http\Controllers\Academico\DivisionController;
use App\Http\Controllers\Academico\EstructuraAcademicaController;
use App\Http\Controllers\Academico\GrupoController;
use App\Http\Controllers\Academico\PlanEstudioController;
use App\Http\Controllers\Academico\ProyectarGruposController;
use App\Http\Controllers\Academico\TurnoController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('academico')->name('academico.')->group(function () {
        Route::get('estructura', [EstructuraAcademicaController::class, 'index'])->name('estructura.index');

        Route::post('anios-lectivos', [AnioLectivoController::class, 'store'])->name('anios-lectivos.store');
        Route::patch('anios-lectivos/{anioLectivo}', [AnioLectivoController::class, 'update'])->name('anios-lectivos.update');
        Route::patch('anios-lectivos/{anioLectivo}/desactivar', [AnioLectivoController::class, 'desactivar'])->name('anios-lectivos.desactivar');
        Route::patch('anios-lectivos/{anioLectivo}/reactivar', [AnioLectivoController::class, 'reactivar'])->name('anios-lectivos.reactivar');
        Route::patch('anios-lectivos/{anioLectivo}/marcar-vigente', [AnioLectivoController::class, 'marcarVigente'])->name('anios-lectivos.marcar-vigente');
        Route::post('anios-lectivos/{anioLectivo}/proyectar-grupos', ProyectarGruposController::class)->name('anios-lectivos.proyectar-grupos');

        Route::post('cursos', [CursoController::class, 'store'])->name('cursos.store');
        Route::patch('cursos/{curso}', [CursoController::class, 'update'])->name('cursos.update');
        Route::patch('cursos/{curso}/desactivar', [CursoController::class, 'desactivar'])->name('cursos.desactivar');
        Route::patch('cursos/{curso}/reactivar', [CursoController::class, 'reactivar'])->name('cursos.reactivar');

        Route::post('divisiones', [DivisionController::class, 'store'])->name('divisiones.store');
        Route::patch('divisiones/{division}', [DivisionController::class, 'update'])->name('divisiones.update');
        Route::patch('divisiones/{division}/desactivar', [DivisionController::class, 'desactivar'])->name('divisiones.desactivar');
        Route::patch('divisiones/{division}/reactivar', [DivisionController::class, 'reactivar'])->name('divisiones.reactivar');

        Route::post('turnos', [TurnoController::class, 'store'])->name('turnos.store');
        Route::patch('turnos/{turno}', [TurnoController::class, 'update'])->name('turnos.update');
        Route::patch('turnos/{turno}/desactivar', [TurnoController::class, 'desactivar'])->name('turnos.desactivar');
        Route::patch('turnos/{turno}/reactivar', [TurnoController::class, 'reactivar'])->name('turnos.reactivar');

        Route::post('planes-estudio', [PlanEstudioController::class, 'store'])->name('planes-estudio.store');
        Route::patch('planes-estudio/{planEstudio}', [PlanEstudioController::class, 'update'])->name('planes-estudio.update');
        Route::patch('planes-estudio/{planEstudio}/desactivar', [PlanEstudioController::class, 'desactivar'])->name('planes-estudio.desactivar');
        Route::patch('planes-estudio/{planEstudio}/reactivar', [PlanEstudioController::class, 'reactivar'])->name('planes-estudio.reactivar');

        Route::get('grupos', [GrupoController::class, 'index'])->name('grupos.index');
        Route::post('grupos', [GrupoController::class, 'store'])->name('grupos.store');
        Route::patch('grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');
        Route::patch('grupos/{grupo}/desactivar', [GrupoController::class, 'desactivar'])->name('grupos.desactivar');
        Route::patch('grupos/{grupo}/reactivar', [GrupoController::class, 'reactivar'])->name('grupos.reactivar');
    });
});

require __DIR__.'/settings.php';
