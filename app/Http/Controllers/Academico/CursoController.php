<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\CursoRequest;
use App\Models\Curso;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CursoController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Curso::class);

        return Inertia::render('academico/catalogos/index', [
            'catalogo' => [
                'clave' => 'cursos',
                'titulo' => 'Cursos',
                'singular' => 'curso',
                'descripcion' => 'Salas, grados y años disponibles para cada nivel.',
                'usa_codigo' => false,
                'usa_nivel' => true,
                'usa_orden' => true,
            ],
            'registros' => Curso::query()
                ->select(['id', 'activo', 'descripcion', 'nivel_id', 'orden'])
                ->with('nivel:id,activo,codigo,descripcion')
                ->orderByDesc('activo')
                ->orderBy('nivel_id')
                ->orderBy('orden')
                ->orderBy('descripcion')
                ->get(),
            'niveles' => $this->niveles(),
        ]);
    }

    public function store(CursoRequest $request): RedirectResponse
    {
        $this->crear(new Curso, $request->validated());
        $this->toast('Curso creado.');

        return back();
    }

    public function update(CursoRequest $request, Curso $curso): RedirectResponse
    {
        $this->actualizar($curso, $request->validated());
        $this->toast('Curso actualizado.');

        return back();
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Curso $curso): RedirectResponse
    {
        $this->cambiarEstado($curso, false);
        $this->toast('Curso desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Curso $curso): RedirectResponse
    {
        $this->cambiarEstado($curso, true);
        $this->toast('Curso reactivado.');

        return back();
    }
}
