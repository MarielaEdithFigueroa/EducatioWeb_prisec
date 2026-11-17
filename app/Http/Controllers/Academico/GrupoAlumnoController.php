<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GestionarGrupoAlumno;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\AsignarAlumnosGrupoRequest;
use App\Models\Alumno;
use App\Models\AlumnoGrupo;
use App\Models\Grupo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GrupoAlumnoController extends Controller
{
    public function index(Grupo $grupo): JsonResponse
    {
        Gate::authorize('view', $grupo);
        Gate::authorize('viewAny', Alumno::class);

        return response()->json([
            'grupo_id' => $grupo->id,
            'alumnos' => $grupo->alumnos()->select(['id', 'legajo', 'apellido', 'nombre'])
                ->orderByDesc('id')->get(),
        ])->header('Cache-Control', 'no-store');
    }

    public function create(Grupo $grupo): Response
    {
        Gate::authorize('update', $grupo);
        Gate::authorize('viewAny', Alumno::class);

        $grupo->load(['nivel', 'curso', 'division', 'turno']);
        $alumnos = Alumno::query()->where('activo', true)
            ->select(['id', 'legajo', 'apellido', 'nombre', 'numero_documento', 'fecha_nacimiento'])
            ->with('ultimaAsignacionGrupo.grupo')->orderByDesc('id')->get();

        return Inertia::render('academico/grupos/asignar-alumnos', [
            'grupo' => ['id' => $grupo->id, 'descripcion' => $grupo->descripcion, 'activo' => $grupo->activo],
            'alumnos' => $alumnos->map(function (Alumno $alumno): array {
                $ultima = $alumno->ultimaAsignacionGrupo;
                $vigente = $ultima !== null && $ultima->fecha_hasta === null ? $ultima : null;

                return [
                    ...$alumno->only(['id', 'legajo', 'apellido', 'nombre', 'numero_documento']),
                    'cohorte_nacimiento' => $alumno->cohorte_nacimiento,
                    'ciclo_lectivo_actual' => $vigente?->grupo->ciclo_lectivo,
                    'grupo_id' => $vigente?->grupo_id,
                    'grupo_descripcion' => $vigente?->grupo_descripcion,
                    'grupo_activo' => $vigente?->grupo->activo,
                    'version_historial' => AlumnoGrupo::version($ultima),
                ];
            }),
        ]);
    }

    public function store(AsignarAlumnosGrupoRequest $request, Grupo $grupo, GestionarGrupoAlumno $gestionar): RedirectResponse
    {
        $resultado = $gestionar->asignarMasivamente($grupo, $request->validated('alumnos'), $request->validated('motivo'));
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Alumnos asignados: {$resultado['asignados']}. Ya pertenecían al grupo: {$resultado['omitidos']}.",
        ]);

        return to_route('academico.grupos.show', $grupo);
    }
}
