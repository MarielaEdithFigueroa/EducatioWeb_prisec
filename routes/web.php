<?php

use App\Http\Controllers\Academico\AdjuntoIntegranteController;
use App\Http\Controllers\Academico\AlumnoController;
use App\Http\Controllers\Academico\AlumnoResponsableController;
use App\Http\Controllers\Academico\AulaController;
use App\Http\Controllers\Academico\CursoController;
use App\Http\Controllers\Academico\DivisionController;
use App\Http\Controllers\Academico\DocumentoAlumnoController;
use App\Http\Controllers\Academico\DocumentoIntegranteController;
use App\Http\Controllers\Academico\FotoAulaController;
use App\Http\Controllers\Academico\GrupoController;
use App\Http\Controllers\Academico\IntegranteController;
use App\Http\Controllers\Academico\NivelController;
use App\Http\Controllers\Academico\PlanEstudioController;
use App\Http\Controllers\Academico\ResponsableController;
use App\Http\Controllers\Academico\TipoDocumentacionIntegranteController;
use App\Http\Controllers\Academico\TurnoController;
use App\Http\Controllers\Sistema\LocalidadController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware('auth')->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::prefix('academico')->name('academico.')->group(function () {
        Route::get('integrantes', [IntegranteController::class, 'index'])->name('integrantes.index');
        Route::get('integrantes/nuevo', [IntegranteController::class, 'create'])->name('integrantes.create');
        Route::post('integrantes', [IntegranteController::class, 'store'])->name('integrantes.store');
        Route::get('integrantes/{integrante}', [IntegranteController::class, 'show'])->name('integrantes.show');
        Route::get('integrantes/{integrante}/editar', [IntegranteController::class, 'edit'])->name('integrantes.edit');
        Route::patch('integrantes/{integrante}', [IntegranteController::class, 'update'])->name('integrantes.update');
        Route::patch('integrantes/{integrante}/desactivar', [IntegranteController::class, 'desactivar'])->name('integrantes.desactivar');
        Route::patch('integrantes/{integrante}/reactivar', [IntegranteController::class, 'reactivar'])->name('integrantes.reactivar');
        Route::get('integrantes/{integrante}/foto', [IntegranteController::class, 'foto'])->name('integrantes.foto');
        Route::get('integrantes/{integrante}/imprimir', [IntegranteController::class, 'imprimir'])->name('integrantes.imprimir');
        Route::prefix('integrantes/{integrante}/documentacion/{tipoDocumentacionIntegrante}')->name('integrantes.documentacion.')->group(function () {
            Route::post('/', [DocumentoIntegranteController::class, 'guardar'])->name('guardar');
            Route::patch('/', [DocumentoIntegranteController::class, 'quitar'])->name('quitar');
            Route::get('/', [DocumentoIntegranteController::class, 'ver'])->name('ver');
        });

        Route::get('tipos-documentacion-integrante', [TipoDocumentacionIntegranteController::class, 'index'])->name('tipos_documentacion_integrante.index');
        Route::post('tipos-documentacion-integrante', [TipoDocumentacionIntegranteController::class, 'store'])->name('tipos_documentacion_integrante.store');
        Route::patch('tipos-documentacion-integrante/{tipoDocumentacionIntegrante}', [TipoDocumentacionIntegranteController::class, 'update'])->name('tipos_documentacion_integrante.update');
        Route::patch('tipos-documentacion-integrante/{tipoDocumentacionIntegrante}/desactivar', [TipoDocumentacionIntegranteController::class, 'desactivar'])->name('tipos_documentacion_integrante.desactivar');
        Route::patch('tipos-documentacion-integrante/{tipoDocumentacionIntegrante}/reactivar', [TipoDocumentacionIntegranteController::class, 'reactivar'])->name('tipos_documentacion_integrante.reactivar');

        Route::get('aulas', [AulaController::class, 'index'])->name('aulas.index');
        Route::get('aulas/nueva', [AulaController::class, 'create'])->name('aulas.create');
        Route::post('aulas', [AulaController::class, 'store'])->name('aulas.store');
        Route::get('aulas/{aula}', [AulaController::class, 'show'])->name('aulas.show');
        Route::get('aulas/{aula}/editar', [AulaController::class, 'edit'])->name('aulas.edit');
        Route::patch('aulas/{aula}', [AulaController::class, 'update'])->name('aulas.update');
        Route::patch('aulas/{aula}/desactivar', [AulaController::class, 'desactivar'])->name('aulas.desactivar');
        Route::patch('aulas/{aula}/reactivar', [AulaController::class, 'reactivar'])->name('aulas.reactivar');

        Route::scopeBindings()->prefix('aulas/{aula}/fotos')->name('aulas.fotos.')->group(function () {
            Route::post('/', [FotoAulaController::class, 'store'])->name('store');
            Route::get('{adjunto}/descargar', [FotoAulaController::class, 'descargar'])->name('descargar');
            Route::get('{adjunto}/miniatura', [FotoAulaController::class, 'miniatura'])->name('miniatura');
            Route::patch('{adjunto}', [FotoAulaController::class, 'update'])->name('update');
            Route::patch('{adjunto}/desactivar', [FotoAulaController::class, 'desactivar'])->name('desactivar');
            Route::patch('{adjunto}/reactivar', [FotoAulaController::class, 'reactivar'])->name('reactivar');
        });

        Route::get('grupos', [GrupoController::class, 'index'])->name('grupos.index');
        Route::get('grupos/nuevo', [GrupoController::class, 'create'])->name('grupos.create');
        Route::post('grupos', [GrupoController::class, 'store'])->name('grupos.store');
        Route::get('grupos/{grupo}', [GrupoController::class, 'show'])->name('grupos.show');
        Route::get('grupos/{grupo}/editar', [GrupoController::class, 'edit'])->name('grupos.edit');
        Route::patch('grupos/{grupo}', [GrupoController::class, 'update'])->name('grupos.update');
        Route::patch('grupos/{grupo}/desactivar', [GrupoController::class, 'desactivar'])->name('grupos.desactivar');
        Route::patch('grupos/{grupo}/reactivar', [GrupoController::class, 'reactivar'])->name('grupos.reactivar');

        Route::get('responsables', [ResponsableController::class, 'index'])->name('responsables.index');
        Route::get('responsables/{responsable}', [ResponsableController::class, 'show'])->name('responsables.show');
        Route::get('responsables/{responsable}/editar', [ResponsableController::class, 'edit'])->name('responsables.edit');
        Route::patch('responsables/{responsable}', [ResponsableController::class, 'update'])->name('responsables.update');

        Route::get('alumnos', [AlumnoController::class, 'index'])->name('alumnos.index');
        Route::get('alumnos/nuevo', [AlumnoController::class, 'create'])->name('alumnos.create');
        Route::post('alumnos', [AlumnoController::class, 'store'])->name('alumnos.store');
        Route::scopeBindings()->prefix('integrantes/{integrante}/adjuntos')->name('integrantes.adjuntos.')->group(function () {
            Route::post('/', [AdjuntoIntegranteController::class, 'store'])->name('store');
            Route::get('{adjunto}/descargar', [AdjuntoIntegranteController::class, 'descargar'])->name('descargar');
            Route::get('{adjunto}/miniatura', [AdjuntoIntegranteController::class, 'miniatura'])->name('miniatura');
            Route::patch('{adjunto}', [AdjuntoIntegranteController::class, 'update'])->name('update');
            Route::patch('{adjunto}/desactivar', [AdjuntoIntegranteController::class, 'desactivar'])->name('desactivar');
            Route::patch('{adjunto}/reactivar', [AdjuntoIntegranteController::class, 'reactivar'])->name('reactivar');
        });

        Route::scopeBindings()->prefix('alumnos/{alumno}/documentacion')->name('alumnos.documentacion.')->group(function () {
            Route::post('/', [DocumentoAlumnoController::class, 'store'])->name('store');
            Route::get('{adjunto}/descargar', [DocumentoAlumnoController::class, 'descargar'])->name('descargar');
            Route::get('{adjunto}/miniatura', [DocumentoAlumnoController::class, 'miniatura'])->name('miniatura');
            Route::patch('{adjunto}', [DocumentoAlumnoController::class, 'update'])->name('update');
            Route::patch('{adjunto}/desactivar', [DocumentoAlumnoController::class, 'desactivar'])->name('desactivar');
            Route::patch('{adjunto}/reactivar', [DocumentoAlumnoController::class, 'reactivar'])->name('reactivar');
        });
        Route::get('alumnos/{alumno}/responsables/buscar', [AlumnoResponsableController::class, 'buscar'])
            ->name('alumnos.responsables.buscar');
        Route::post('alumnos/{alumno}/responsables/vincular', [AlumnoResponsableController::class, 'store'])
            ->name('alumnos.responsables.vincular');
        Route::get('alumnos/{alumno}/responsables/nuevo', [ResponsableController::class, 'create'])
            ->name('alumnos.responsables.create');
        Route::post('alumnos/{alumno}/responsables/nuevo', [ResponsableController::class, 'store'])
            ->name('alumnos.responsables.store');
        Route::get('alumnos/{alumno}', [AlumnoController::class, 'show'])->name('alumnos.show');
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
