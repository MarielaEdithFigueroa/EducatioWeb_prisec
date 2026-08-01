<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarCursoRequest;
use App\Models\Curso;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class CursoController extends Controller
{
    public function store(GuardarCursoRequest $request, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->crear(new Curso, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso creado.']);

        return to_route('academico.estructura.index');
    }

    public function update(GuardarCursoRequest $request, Curso $curso, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($curso, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso actualizado.']);

        return to_route('academico.estructura.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, Curso $curso, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($curso, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso desactivado.']);

        return to_route('academico.estructura.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, Curso $curso, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($curso, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso reactivado.']);

        return to_route('academico.estructura.index');
    }
}
