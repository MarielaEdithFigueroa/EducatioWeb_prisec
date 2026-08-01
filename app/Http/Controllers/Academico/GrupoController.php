<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GuardarGrupo;
use App\Actions\Academico\PersistirEntidadAcademica;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CambiarEstadoAcademicoRequest;
use App\Http\Requests\Academico\GuardarGrupoRequest;
use App\Http\Requests\Academico\ListarGruposRequest;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class GrupoController extends Controller
{
    public function index(ListarGruposRequest $request): Response
    {
        $filtros = $request->validated();
        $estado = $filtros['estado'] ?? 'activos';
        $tablaGrupos = (new Grupo)->getTable();
        $tablaAnios = (new AnioLectivo)->getTable();
        $tablaCursos = (new Curso)->getTable();
        $tablaDivisiones = (new Division)->getTable();

        $grupos = Grupo::query()
            ->with([
                'anioLectivo:id,anio,estado,activo',
                'planEstudio.nivel:id,codigo,descripcion',
                'curso.nivel:id,codigo,descripcion',
                'division:id,nivel_id,descripcion,activo',
                'turno:id,nivel_id,descripcion,activo',
            ])
            ->when(isset($filtros['anio_lectivo_id']), fn ($query) => $query->where('anio_lectivo_id', $filtros['anio_lectivo_id']))
            ->when(isset($filtros['nivel_id']), fn ($query) => $query->whereHas('curso', fn ($curso) => $curso->where('nivel_id', $filtros['nivel_id'])))
            ->when($estado === 'activos', fn ($query) => $query->where('activo', true))
            ->when($estado === 'inactivos', fn ($query) => $query->where('activo', false))
            ->orderByDesc(AnioLectivo::query()->select('anio')->whereColumn("{$tablaAnios}.id", "{$tablaGrupos}.anio_lectivo_id"))
            ->orderBy(Curso::query()->select('orden')->whereColumn("{$tablaCursos}.id", "{$tablaGrupos}.curso_id"))
            ->orderBy(Division::query()->select('orden')->whereColumn("{$tablaDivisiones}.id", "{$tablaGrupos}.division_id"))
            ->get();

        return Inertia::render('academico/grupos', [
            'grupos' => $grupos,
            'filtros' => [
                'anio_lectivo_id' => $filtros['anio_lectivo_id'] ?? null,
                'nivel_id' => $filtros['nivel_id'] ?? null,
                'estado' => $estado,
            ],
            'niveles' => Nivel::query()->activos()->orderBy('orden')->get(['id', 'codigo', 'descripcion']),
            'aniosLectivos' => AnioLectivo::query()->select(['id', 'activo', 'anio', 'estado'])->withCount(['grupos as grupos_activos_count' => fn ($query) => $query->where('activo', true)])->orderByDesc('anio')->get(),
            'cursos' => Curso::query()->orderBy('nivel_id')->orderBy('orden')->get(['id', 'activo', 'nivel_id', 'descripcion', 'orden']),
            'divisiones' => Division::query()->orderBy('nivel_id')->orderBy('orden')->get(['id', 'activo', 'nivel_id', 'descripcion', 'orden']),
            'turnos' => Turno::query()->orderBy('nivel_id')->orderBy('orden')->get(['id', 'activo', 'nivel_id', 'descripcion', 'orden']),
            'planesEstudio' => PlanEstudio::query()->orderBy('nivel_id')->orderBy('descripcion')->get(['id', 'activo', 'nivel_id', 'codigo', 'descripcion']),
        ]);
    }

    public function store(GuardarGrupoRequest $request, GuardarGrupo $guardarGrupo): RedirectResponse
    {
        $resultado = $guardarGrupo->ejecutar($request->validated());

        if ($resultado['reactivado']) {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'El grupo existente fue reactivado.']);
        } else {
            Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo creado.']);
        }

        return to_route('academico.grupos.index');
    }

    public function update(GuardarGrupoRequest $request, Grupo $grupo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->actualizar($grupo, $request->validated());
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo actualizado.']);

        return to_route('academico.grupos.index');
    }

    public function desactivar(CambiarEstadoAcademicoRequest $request, Grupo $grupo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($grupo, false);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo desactivado.']);

        return to_route('academico.grupos.index');
    }

    public function reactivar(CambiarEstadoAcademicoRequest $request, Grupo $grupo, PersistirEntidadAcademica $persistir): RedirectResponse
    {
        $persistir->cambiarActivo($grupo, true);
        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo reactivado.']);

        return to_route('academico.grupos.index');
    }
}
