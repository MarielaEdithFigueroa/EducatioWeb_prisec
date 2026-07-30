<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CursoStoreRequest;
use App\Http\Requests\Academico\CursoUpdateRequest;
use App\Models\Curso;
use App\Models\Nivel;
use App\Support\Auditoria\RegistraAuditoria;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends Controller
{
    use RegistraAuditoria;

    public function index(): Response
    {
        $this->authorize('viewAny', Curso::class);

        return Inertia::render('academico/cursos/index', [
            'cursos' => Curso::with('nivel:id,codigo,descripcion')
                ->orderBy('nivel_id')
                ->orderBy('orden')
                ->get(['id', 'activo', 'nivel_id', 'codigo', 'descripcion', 'orden']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Curso::class);

        return Inertia::render('academico/cursos/form', [
            'niveles' => $this->nivelesOptions(),
        ]);
    }

    public function store(CursoStoreRequest $request): RedirectResponse
    {
        $this->authorize('create', Curso::class);

        DB::transaction(function () use ($request) {
            $curso = Curso::create($request->validated());
            $this->auditar('create', $curso, null, $curso->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso creado.']);

        return to_route('academico.cursos.index');
    }

    public function edit(Curso $curso): Response
    {
        $this->authorize('update', $curso);

        return Inertia::render('academico/cursos/form', [
            'curso' => $curso->only(['id', 'activo', 'nivel_id', 'codigo', 'descripcion', 'orden']),
            'niveles' => $this->nivelesOptions(),
        ]);
    }

    public function update(CursoUpdateRequest $request, Curso $curso): RedirectResponse
    {
        $this->authorize('update', $curso);

        DB::transaction(function () use ($request, $curso) {
            $anterior = $curso->attributesToArray();
            $curso->update($request->validated());
            $this->auditar('update', $curso, $anterior, $curso->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso actualizado.']);

        return to_route('academico.cursos.index');
    }

    public function desactivar(Curso $curso): RedirectResponse
    {
        $this->authorize('update', $curso);

        if ($curso->activo) {
            DB::transaction(function () use ($curso) {
                $anterior = $curso->attributesToArray();
                $curso->update(['activo' => false]);
                $this->auditar('deactivate', $curso, $anterior, $curso->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso dado de baja.']);

        return to_route('academico.cursos.index');
    }

    public function reactivar(Curso $curso): RedirectResponse
    {
        $this->authorize('update', $curso);

        if (! $curso->activo) {
            DB::transaction(function () use ($curso) {
                $anterior = $curso->attributesToArray();
                $curso->update(['activo' => true]);
                $this->auditar('reactivate', $curso, $anterior, $curso->attributesToArray());
            });
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Curso reactivado.']);

        return to_route('academico.cursos.index');
    }

    /**
     * Opciones de nivel para el select del formulario. Incluye inactivos porque
     * un curso histórico puede apuntar a un nivel dado de baja (ver core.md).
     *
     * @return Collection<int, Nivel>
     */
    private function nivelesOptions(): Collection
    {
        return Nivel::orderBy('descripcion')->get(['id', 'codigo', 'descripcion', 'activo']);
    }
}
