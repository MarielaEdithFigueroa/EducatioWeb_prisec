<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarDivisionRequest;
use App\Models\Division;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class DivisionController extends Controller
{
    public function store(GuardarDivisionRequest $request, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->crear(new Division, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'División creada.']);

        return to_route('academico.estructura.index');
    }

    public function update(GuardarDivisionRequest $request, Division $division, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($division, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'División actualizada.']);

        return to_route('academico.estructura.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, Division $division, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($division, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'División desactivada.']);

        return to_route('academico.estructura.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, Division $division, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($division, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'División reactivada.']);

        return to_route('academico.estructura.index');
    }
}
