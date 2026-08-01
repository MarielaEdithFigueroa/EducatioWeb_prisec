<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EstructuraAcademicaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Nivel::class);

        return Inertia::render('academico/estructura', [
            'niveles' => Nivel::query()->orderBy('orden')->get(['id', 'activo', 'codigo', 'descripcion', 'orden']),
            'aniosLectivos' => AnioLectivo::query()->select(['id', 'activo', 'anio', 'estado'])->withCount('grupos')->orderByDesc('anio')->get(),
            'cursos' => Curso::query()->select(['id', 'activo', 'nivel_id', 'descripcion', 'orden'])->with('nivel:id,codigo,descripcion')->withCount('grupos')->orderBy('nivel_id')->orderBy('orden')->get(),
            'divisiones' => Division::query()->select(['id', 'activo', 'nivel_id', 'descripcion', 'orden'])->with('nivel:id,codigo,descripcion')->withCount('grupos')->orderBy('nivel_id')->orderBy('orden')->get(),
            'turnos' => Turno::query()->select(['id', 'activo', 'nivel_id', 'descripcion', 'orden'])->with('nivel:id,codigo,descripcion')->withCount('grupos')->orderBy('nivel_id')->orderBy('orden')->get(),
            'planesEstudio' => PlanEstudio::query()->select(['id', 'activo', 'nivel_id', 'codigo', 'descripcion'])->with('nivel:id,codigo,descripcion')->withCount('grupos')->orderBy('nivel_id')->orderBy('descripcion')->get(),
        ]);
    }
}
