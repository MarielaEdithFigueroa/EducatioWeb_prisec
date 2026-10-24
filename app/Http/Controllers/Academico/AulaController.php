<?php

namespace App\Http\Controllers\Academico;

use App\Http\Requests\Academico\AulaRequest;
use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Models\Adjunto;
use App\Models\Aula;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AulaController extends CatalogoController
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Aula::class);

        return Inertia::render('academico/aulas/index', [
            'aulas' => Aula::query()->orderBy('descripcion')->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Aula::class);

        return Inertia::render('academico/aulas/formulario', ['aula' => null]);
    }

    public function store(AulaRequest $request): RedirectResponse
    {
        $this->guardar(new Aula, $request->datos());
        $this->toast('Aula creada correctamente.');

        return to_route('academico.aulas.index');
    }

    public function show(Aula $aula): Response
    {
        Gate::authorize('view', $aula);

        return Inertia::render('academico/aulas/show', [
            'aula' => $aula,
            'fotos' => fn () => $aula->adjuntos()->with('usuario:id,login')
                ->orderByDesc('activo')->orderByDesc('fecha_registro')->orderByDesc('adjuntos.id')
                ->get()->map(fn (Adjunto $adjunto): array => [
                    'id' => $adjunto->id,
                    'activo' => $adjunto->activo,
                    'nombre_original' => $adjunto->nombre_original,
                    'mime' => $adjunto->mime,
                    'tamano_bytes' => $adjunto->tamano_bytes,
                    'observaciones' => $adjunto->observaciones,
                    'fecha_registro' => $adjunto->fecha_registro->toIso8601String(),
                    'usuario' => $adjunto->usuario?->login ?? '',
                ]),
            'configFotos' => [
                'max_bytes' => config('adjuntos.max_kb') * 1024,
                'extensiones' => config('adjuntos.extensiones_fotos'),
            ],
        ]);
    }

    public function edit(Aula $aula): Response
    {
        Gate::authorize('update', $aula);

        return Inertia::render('academico/aulas/formulario', ['aula' => $aula]);
    }

    public function update(AulaRequest $request, Aula $aula): RedirectResponse
    {
        $this->guardar($aula, $request->datos());
        $this->toast('Aula actualizada correctamente.');

        return to_route('academico.aulas.show', $aula);
    }

    public function desactivar(CambiarEstadoCatalogoRequest $request, Aula $aula): RedirectResponse
    {
        $this->cambiarEstado($aula, false);
        $this->toast('Aula desactivada correctamente.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Aula $aula): RedirectResponse
    {
        $this->cambiarEstado($aula, true);
        $this->toast('Aula reactivada correctamente.');

        return back();
    }

    /** @param array<string, mixed> $datos */
    private function guardar(Aula $aula, array $datos): void
    {
        try {
            $aula->exists ? $this->actualizar($aula, $datos) : $this->crear($aula, $datos);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'descripcion' => 'Ya existe un aula con esa descripción. Si está inactiva, podés reactivarla desde el listado.',
            ]);
        }
    }
}
