<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\GrupoStoreRequest;
use App\Http\Requests\Academico\GrupoUpdateRequest;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Turno;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GrupoController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', Grupo::class);

        return Inertia::render('academico/grupos/index', [
            'grupos' => Grupo::with([
                'anioLectivo:id,anio,activo',
                'curso:id,nivel_id,codigo,descripcion,activo',
                'curso.nivel:id,codigo,descripcion',
                'division:id,codigo,descripcion,activo',
                'turno:id,codigo,descripcion,activo',
            ])
                ->orderByDesc('anio_lectivo_id')
                ->orderBy('curso_id')
                ->get(['id', 'activo', 'anio_lectivo_id', 'curso_id', 'division_id', 'turno_id']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Grupo::class);

        return Inertia::render('academico/grupos/form', $this->opciones());
    }

    public function store(GrupoStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Grupo::class);

        DB::transaction(function () use ($request) {
            $grupo = Grupo::create($request->validated());
            $this->auditar('create', $grupo, null, $grupo->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo creado.']);

        return to_route('academico.grupos.index');
    }

    public function edit(Grupo $grupo): Response
    {
        $this->authorize('update', $grupo);

        return Inertia::render('academico/grupos/form', [
            'grupo' => $grupo->only(['id', 'activo', 'anio_lectivo_id', 'curso_id', 'division_id', 'turno_id']),
            ...$this->opciones(),
        ]);
    }

    public function update(GrupoUpdateRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->authorize('update', $grupo);

        DB::transaction(function () use ($request, $grupo) {
            $anterior = $grupo->attributesToArray();
            $grupo->update($request->validated());
            $this->auditar('update', $grupo, $anterior, $grupo->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo actualizado.']);

        return to_route('academico.grupos.index');
    }

    public function desactivar(Grupo $grupo): RedirectResponse
    {
        $this->authorize('update', $grupo);

        if ($grupo->activo) {
            DB::transaction(function () use ($grupo) {
                $anterior = $grupo->attributesToArray();
                $grupo->update(['activo' => false]);
                $this->auditar('deactivate', $grupo, $anterior, $grupo->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo dado de baja.']);

        return to_route('academico.grupos.index');
    }

    public function reactivar(Grupo $grupo): RedirectResponse
    {
        $this->authorize('update', $grupo);

        if (! $grupo->activo) {
            DB::transaction(function () use ($grupo) {
                $anterior = $grupo->attributesToArray();
                $grupo->update(['activo' => true]);
                $this->auditar('reactivate', $grupo, $anterior, $grupo->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Grupo reactivado.']);

        return to_route('academico.grupos.index');
    }

    /**
     * Opciones para los selects del formulario. Incluyen inactivos porque un
     * grupo histórico puede apuntar a catálogos dados de baja (ver core.md).
     *
     * @return array<string, mixed>
     */
    private function opciones(): array
    {
        return [
            'aniosLectivos' => AnioLectivo::orderByDesc('anio')->get(['id', 'anio', 'vigente', 'activo']),
            'cursos' => Curso::with('nivel:id,codigo,descripcion')
                ->orderBy('nivel_id')
                ->orderBy('orden')
                ->get(['id', 'nivel_id', 'codigo', 'descripcion', 'activo']),
            'divisiones' => Division::orderBy('codigo')->get(['id', 'codigo', 'descripcion', 'activo']),
            'turnos' => Turno::orderBy('codigo')->get(['id', 'codigo', 'descripcion', 'activo']),
        ];
    }
}
