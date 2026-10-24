<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\GrupoRequest;
use App\Http\Requests\Academico\IndexGrupoRequest;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\PlanEstudio;
use App\Models\Turno;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GrupoController extends CatalogoController
{
    private const RELACIONES = ['nivel', 'curso', 'division', 'turno', 'planEstudio'];

    public function index(IndexGrupoRequest $request): Response
    {
        $filters = $request->filters();
        $consulta = Grupo::query()->with(self::RELACIONES);

        foreach (['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id', 'plan_estudio_id'] as $campo) {
            if ($filters[$campo] !== '') {
                $consulta->where($campo, (int) $filters[$campo]);
            }
        }

        if ($filters['estado'] !== '') {
            $consulta->where('activo', $filters['estado'] === 'activos');
        }

        $grupos = $consulta->orderByDesc('ciclo_lectivo')->orderBy('nivel_id')->orderBy('curso_id')
            ->orderBy('division_id')->orderBy('turno_id')->get();

        return Inertia::render('academico/grupos/index', [
            'grupos' => $grupos->map(fn (Grupo $grupo): array => $this->detalle($grupo)),
            'filters' => $filters,
            'resultados' => ['total' => Grupo::query()->count(), 'filtrados' => $grupos->count()],
            'catalogos' => $this->catalogos(),
            'ciclosLectivos' => fn (): array => range(
                min(now()->year, (int) (Grupo::query()->min('ciclo_lectivo') ?? now()->year)),
                now()->year + 1,
            ),
        ]);
    }

    public function create(IndexGrupoRequest $request): Response
    {
        Gate::authorize('create', Grupo::class);

        return Inertia::render('academico/grupos/formulario', [
            'grupo' => null,
            'filters' => $request->filters(),
            'catalogos' => $this->catalogos(),
            'iniciales' => $request->session()->get('nuevo_grupo', ['ciclo_lectivo' => now()->year]),
        ]);
    }

    public function store(GrupoRequest $request): RedirectResponse
    {
        $this->guardar(new Grupo, $request->datos());
        $this->toast('Grupo creado correctamente.');

        if ($request->boolean('crear_otro')) {
            return to_route('academico.grupos.create', ['filtros' => $request->validated('filtros', [])])
                ->with('nuevo_grupo', $request->safe()->only(['ciclo_lectivo', 'nivel_id', 'turno_id', 'plan_estudio_id']));
        }

        return to_route('academico.grupos.index', $request->validated('filtros', []));
    }

    public function show(IndexGrupoRequest $request, Grupo $grupo): Response
    {
        Gate::authorize('view', $grupo);

        return Inertia::render('academico/grupos/show', [
            'grupo' => $this->detalle($grupo->load(self::RELACIONES)),
            'filters' => $request->filters(),
        ]);
    }

    public function edit(IndexGrupoRequest $request, Grupo $grupo): Response
    {
        Gate::authorize('update', $grupo);

        return Inertia::render('academico/grupos/formulario', [
            'grupo' => $this->detalle($grupo->load(self::RELACIONES)),
            'filters' => $request->filters(),
            'catalogos' => $this->catalogos(),
            'iniciales' => [],
        ]);
    }

    public function update(GrupoRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->guardar($grupo, $request->datos());
        $this->toast('Grupo actualizado correctamente.');

        return to_route('academico.grupos.show', ['grupo' => $grupo->id, 'filtros' => $request->validated('filtros', [])]);
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->cambiarEstado($grupo, false);
        $this->toast('Grupo desactivado correctamente.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Grupo $grupo): RedirectResponse
    {
        $this->cambiarEstado($grupo, true);
        $this->toast('Grupo reactivado correctamente.');

        return back();
    }

    /** @param array<string, mixed> $datos */
    private function guardar(Grupo $grupo, array $datos): void
    {
        try {
            $grupo->exists ? $this->actualizar($grupo, $datos) : $this->crear($grupo, $datos);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'ciclo_lectivo' => 'Ya existe un grupo con esa composición. Podés buscarlo en el listado y reactivarlo si está inactivo.',
            ]);
        }
    }

    /** @return array<string, mixed> */
    private function detalle(Grupo $grupo): array
    {
        return [
            ...$grupo->only(['id', 'activo', 'ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id', 'plan_estudio_id']),
            'descripcion' => $grupo->descripcion,
            'nivel' => $grupo->nivel->only(['id', 'descripcion', 'activo']),
            'curso' => $grupo->curso->only(['id', 'descripcion', 'activo']),
            'division' => $grupo->division->only(['id', 'descripcion', 'activo']),
            'turno' => $grupo->turno->only(['id', 'descripcion', 'activo']),
            'plan_estudio' => $grupo->planEstudio?->only(['id', 'descripcion', 'activo']),
        ];
    }

    /** @return array<string, mixed> */
    private function catalogos(): array
    {
        return [
            'niveles' => $this->niveles(),
            'cursos' => Curso::query()->select(['id', 'descripcion', 'activo', 'nivel_id'])->orderBy('nivel_id')->orderBy('orden')->orderBy('descripcion')->get(),
            'divisiones' => Division::query()->select(['id', 'descripcion', 'activo'])->orderBy('orden')->orderBy('descripcion')->get(),
            'turnos' => Turno::query()->select(['id', 'descripcion', 'activo'])->orderBy('orden')->orderBy('descripcion')->get(),
            'planes_estudio' => PlanEstudio::query()->select(['id', 'descripcion', 'activo', 'nivel_id'])->orderBy('nivel_id')->orderBy('orden')->orderBy('descripcion')->get(),
        ];
    }
}
