<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GestionarGrupoAlumno;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarGrupoAlumnoRequest;
use App\Models\Alumno;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AlumnoGrupoController extends Controller
{
    public function update(CambiarGrupoAlumnoRequest $request, Alumno $alumno, GestionarGrupoAlumno $gestionar): RedirectResponse
    {
        $gestionar->cambiar(
            $alumno,
            $request->validated('grupo_id') === null ? null : $request->integer('grupo_id'),
            $request->validated('version_historial'),
            $request->validated('motivo'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Asignación de grupo guardada.']);

        return to_route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'grupos']);
    }
}
