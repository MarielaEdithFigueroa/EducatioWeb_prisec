<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GestionarResponsableAlumno;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\CrearResponsableVinculadoRequest;
use App\Http\Requests\Academico\IndexResponsableRequest;
use App\Http\Requests\Academico\ResponsableRequest;
use App\Models\Alumno;
use App\Models\Ciudad;
use App\Models\Provincia;
use App\Models\Responsable;
use App\Models\TipoDocumento;
use App\Models\TipoVinculo;
use App\Services\TelefonoNormalizer;
use App\Support\Auditoria\RegistraAuditoria;
use App\Support\Database\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ResponsableController extends Controller
{
    use RegistraAuditoria;

    public function index(IndexResponsableRequest $request): Response
    {
        Gate::authorize('viewAny', Responsable::class);

        $filters = $request->filters();

        $query = Responsable::query()
            ->select([
                'id',
                'activo',
                'apellido',
                'nombre',
                'nombre_elegido',
                'tipo_documento_id',
                'numero_documento',
                'cuilt',
                'email',
                'telefono_e164',
            ])
            ->with([
                'tipoDocumento:id,nombre',
                'alumnosResponsables:id,activo,alumno_id,responsable_id',
                'alumnosResponsables.alumno:id,activo,legajo,apellido,nombre',
            ])
            ->when($filters['buscar'] !== '', function (Builder $query) use ($filters): void {
                $term = LikePattern::contains($filters['buscar']);
                $numero = preg_match('/^[+\d\s().-]+$/', $filters['buscar']) === 1
                    ? (preg_replace('/\D/', '', $filters['buscar']) ?? '')
                    : '';

                $query->where(function (Builder $query) use ($term, $numero): void {
                    $query
                        ->whereRaw("apellido LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("nombre LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("nombre_elegido LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("numero_documento LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("email LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("telefono_e164 LIKE ? ESCAPE '!'", [$term])
                        ->orWhereHas('alumnosResponsables.alumno', function (Builder $alumnos) use ($term): void {
                            $alumnos->where(function (Builder $alumnos) use ($term): void {
                                $alumnos
                                    ->whereRaw("apellido LIKE ? ESCAPE '!'", [$term])
                                    ->orWhereRaw("nombre LIKE ? ESCAPE '!'", [$term])
                                    ->orWhereRaw("legajo LIKE ? ESCAPE '!'", [$term]);
                            });
                        });

                    if ($numero !== '') {
                        $query
                            ->orWhereRaw("cuilt LIKE ? ESCAPE '!'", [LikePattern::contains($numero)])
                            ->orWhereRaw("telefono_e164 LIKE ? ESCAPE '!'", [LikePattern::contains($numero)]);
                    }
                });
            })
            ->when(
                $filters['provincia_id'] !== '',
                fn (Builder $query) => $query->whereIn(
                    'ciudad_id',
                    Ciudad::query()
                        ->select('id')
                        ->where('provincia_id', (int) $filters['provincia_id']),
                ),
            )
            ->when(
                $filters['ciudad_id'] !== '',
                fn (Builder $query) => $query->where('ciudad_id', (int) $filters['ciudad_id']),
            )
            ->when(
                $filters['solo_activos'],
                fn (Builder $query) => $query->where('activo', true),
            );

        $totalResponsables = Responsable::query()->count();
        $responsablesFiltrados = (clone $query)->count();

        return Inertia::render('academico/responsables/index', [
            'responsables' => $query
                ->orderByDesc('activo')
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->orderBy('id')
                ->get(),
            'filters' => $filters,
            'resultados' => [
                'total' => $totalResponsables,
                'filtrados' => $responsablesFiltrados,
            ],
            'provincias' => Provincia::query()
                ->select(['id', 'activo', 'nombre'])
                ->where(function (Builder $query): void {
                    $query
                        ->where('activo', true)
                        ->orWhereHas('ciudades.responsables');
                })
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Provincia $provincia) => [
                    'value' => $provincia->id,
                    'label' => $provincia->nombre.($provincia->activo ? '' : ' (inactiva)'),
                ]),
            'ciudades' => Ciudad::query()
                ->select(['id', 'activo', 'provincia_id', 'nombre'])
                ->with('provincia:id,nombre')
                ->where(function (Builder $query): void {
                    $query
                        ->where('activo', true)
                        ->orWhereHas('responsables');
                })
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Ciudad $ciudad) => [
                    'value' => $ciudad->id,
                    'label' => $ciudad->nombre.($ciudad->activo ? '' : ' (inactiva)'),
                    'provincia_id' => $ciudad->provincia_id,
                ]),
        ]);
    }

    public function show(Responsable $responsable): Response
    {
        Gate::authorize('view', $responsable);

        $responsable->load([
            'tipoDocumento:id,nombre',
            'ciudad:id,activo,provincia_id,nombre',
            'ciudad.provincia:id,nombre',
            'ciudadLaboral:id,activo,provincia_id,nombre',
            'ciudadLaboral.provincia:id,nombre',
            'alumnosResponsables:id,activo,alumno_id,responsable_id,vinculo_id',
            'alumnosResponsables.tipoVinculo:id,nombre',
            'alumnosResponsables.alumno:id,activo,legajo,apellido,nombre',
        ]);

        return Inertia::render('academico/responsables/show', [
            'responsable' => [
                ...$responsable->toArray(),
                'fecha_nacimiento' => $responsable->fecha_nacimiento?->format('Y-m-d'),
            ],
        ]);
    }

    public function edit(Responsable $responsable): Response
    {
        Gate::authorize('update', $responsable);

        $ciudadesActuales = collect([$responsable->ciudad_id, $responsable->ciudad_laboral_id])
            ->filter()->values();

        return Inertia::render('academico/responsables/formulario', [
            'alumno' => null,
            'responsable' => [
                ...$responsable->toArray(),
                'fecha_nacimiento' => $responsable->fecha_nacimiento?->format('Y-m-d'),
                'telefono' => $responsable->telefono_original ?? $responsable->telefono_e164,
                'telefono_laboral' => $responsable->telefono_laboral_original ?? $responsable->telefono_laboral_e164,
                'telefono_es_fijo' => TelefonoNormalizer::esLineaFija($responsable->telefono_e164),
                'telefono_laboral_es_fijo' => TelefonoNormalizer::esLineaFija($responsable->telefono_laboral_e164),
            ],
            'tiposDocumento' => TipoDocumento::query()->select(['id', 'nombre'])->orderBy('nombre')->get(),
            'ciudades' => Ciudad::query()
                ->select(['id', 'activo', 'provincia_id', 'nombre', 'codigo_postal'])
                ->with('provincia:id,nombre')
                ->where(fn (Builder $query) => $query->where('activo', true)->orWhereIn('id', $ciudadesActuales))
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get(),
            'tiposVinculo' => [],
        ]);
    }

    public function update(ResponsableRequest $request, Responsable $responsable): RedirectResponse
    {
        DB::transaction(function () use ($request, $responsable): void {
            $anterior = $responsable->attributesToArray();
            $responsable->fill($request->datosResponsable());

            if (! $responsable->isDirty()) {
                return;
            }

            $responsable->save();
            $responsable->refresh();
            $this->auditar('update', $responsable, $anterior, $responsable->attributesToArray());
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Responsable actualizado.']);

        return to_route('academico.responsables.show', $responsable);
    }

    public function create(Alumno $alumno): Response
    {
        Gate::authorize('update', $alumno);
        Gate::authorize('create', Responsable::class);

        return Inertia::render('academico/responsables/formulario', [
            'responsable' => null,
            'alumno' => [
                'id' => $alumno->id,
                'legajo' => $alumno->legajo,
                'apellido' => $alumno->apellido,
                'nombre' => $alumno->nombre,
            ],
            'tiposDocumento' => TipoDocumento::query()
                ->select(['id', 'nombre'])
                ->orderBy('nombre')
                ->get(),
            'ciudades' => Ciudad::query()
                ->select(['id', 'activo', 'provincia_id', 'nombre', 'codigo_postal'])
                ->with('provincia:id,nombre')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
            'tiposVinculo' => TipoVinculo::query()
                ->select(['id', 'nombre'])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(
        CrearResponsableVinculadoRequest $request,
        Alumno $alumno,
        GestionarResponsableAlumno $gestionar,
    ): RedirectResponse {
        $gestionar->crearYVincular(
            $alumno,
            $request->datosResponsable(),
            $request->datosVinculo(),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Responsable creado y vinculado.']);

        return to_route('academico.alumnos.show', [
            'alumno' => $alumno,
            'seccion' => 'responsables',
        ]);
    }
}
