<?php

use App\Http\Controllers\Academico\AlumnoController;
use App\Http\Controllers\Academico\CursoController;
use App\Http\Controllers\Academico\DivisionController;
use App\Http\Controllers\Academico\NivelController;
use App\Http\Controllers\Academico\PlanEstudioController;
use App\Http\Controllers\Academico\TurnoController;
use App\Http\Controllers\Sistema\LocalidadController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('academico')->name('academico.')->group(function () {
        Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
        Route::get('alumnos/nuevo', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::get('alumnos/{alumno}/editar', [AlumnoController::class, 'edit'])->name('alumnos.edit');
        Route::patch('alumnos/{alumno}', [AlumnoController::class, 'update'])->name('alumnos.update');
        Route::patch('alumnos/{alumno}/desactivar', [AlumnoController::class, 'desactivar'])->name('alumnos.desactivar');
        Route::patch('alumnos/{alumno}/reactivar', [AlumnoController::class, 'reactivar'])->name('alumnos.reactivar');

        Route::get('niveles', [NivelController::class, 'index'])->name('niveles.index');
        Route::post('niveles', [NivelController::class, 'store'])->name('niveles.store');
        Route::patch('niveles/{nivel}', [NivelController::class, 'update'])->name('niveles.update');
        Route::patch('niveles/{nivel}/desactivar', [NivelController::class, 'desactivar'])->name('niveles.desactivar');
        Route::patch('niveles/{nivel}/reactivar', [NivelController::class, 'reactivar'])->name('niveles.reactivar');

        Route::get('planes-estudio', [PlanEstudioController::class, 'index'])->name('planes_estudio.index');
        Route::post('planes-estudio', [PlanEstudioController::class, 'store'])->name('planes_estudio.store');
        Route::patch('planes-estudio/{planEstudio}', [PlanEstudioController::class, 'update'])->name('planes_estudio.update');
        Route::patch('planes-estudio/{planEstudio}/desactivar', [PlanEstudioController::class, 'desactivar'])->name('planes_estudio.desactivar');
        Route::patch('planes-estudio/{planEstudio}/reactivar', [PlanEstudioController::class, 'reactivar'])->name('planes_estudio.reactivar');

        Route::get('turnos', [TurnoController::class, 'index'])->name('turnos.index');
        Route::post('turnos', [TurnoController::class, 'store'])->name('turnos.store');
        Route::patch('turnos/{turno}', [TurnoController::class, 'update'])->name('turnos.update');
        Route::patch('turnos/{turno}/desactivar', [TurnoController::class, 'desactivar'])->name('turnos.desactivar');
        Route::patch('turnos/{turno}/reactivar', [TurnoController::class, 'reactivar'])->name('turnos.reactivar');

        Route::get('cursos', [CursoController::class, 'index'])->name('cursos.index');
        Route::post('cursos', [CursoController::class, 'store'])->name('cursos.store');
        Route::patch('cursos/{curso}', [CursoController::class, 'update'])->name('cursos.update');
        Route::patch('cursos/{curso}/desactivar', [CursoController::class, 'desactivar'])->name('cursos.desactivar');
        Route::patch('cursos/{curso}/reactivar', [CursoController::class, 'reactivar'])->name('cursos.reactivar');

        Route::get('divisiones', [DivisionController::class, 'index'])->name('divisiones.index');
        Route::post('divisiones', [DivisionController::class, 'store'])->name('divisiones.store');
        Route::patch('divisiones/{division}', [DivisionController::class, 'update'])->name('divisiones.update');
        Route::patch('divisiones/{division}/desactivar', [DivisionController::class, 'desactivar'])->name('divisiones.desactivar');
        Route::patch('divisiones/{division}/reactivar', [DivisionController::class, 'reactivar'])->name('divisiones.reactivar');
    });

    Route::prefix('sistema')->name('sistema.')->group(function () {
        Route::get('localidades', [LocalidadController::class, 'index'])->name('localidades.index');
        Route::post('localidades', [LocalidadController::class, 'store'])->name('localidades.store');
        Route::patch('localidades/{localidad}', [LocalidadController::class, 'update'])->name('localidades.update');
        Route::patch('localidades/{localidad}/desactivar', [LocalidadController::class, 'desactivar'])->name('localidades.desactivar');
        Route::patch('localidades/{localidad}/reactivar', [LocalidadController::class, 'reactivar'])->name('localidades.reactivar');
    });
});

require __DIR__.'/settings.php';
