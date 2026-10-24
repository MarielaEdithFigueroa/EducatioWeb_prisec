<?php

namespace App\Http\Controllers\Academico;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\AlumnoRequest;
use App\Http\Requests\Academico\CambiarEstadoAlumnoRequest;
use App\Http\Requests\Academico\DarBajaAlumnoRequest;
use App\Http\Requests\Academico\IndexAlumnoRequest;
use App\Http\Requests\Academico\VerAlumnoRequest;
use App\Models\Adjunto;
use App\Models\Alumno;
use App\Models\AlumnoResponsable;
use App\Models\Ciudad;
use App\Models\GrupoSanguineo;
use App\Models\MotivoBaja;
use App\Models\Nacionalidad;
use App\Models\Provincia;
use App\Models\TipoDocumento;
use App\Models\TipoVinculo;
use App\Support\Auditoria\RegistraAuditoria;
use App\Support\Database\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class AlumnoController extends Controller
{
    use RegistraAuditoria;

    public function index(IndexAlumnoRequest $request): Response
    {
        Gate::authorize('viewAny', Alumno::class);

        $filters = $request->filters();
        $query = Alumno::query()
            ->select([
                'id',
                'activo',
                'legajo',
                'apellido',
                'nombre',
                'nombre_elegido',
                'tipo_documento_id',
                'numero_documento',
                'fecha_nacimiento',
                'ciudad_id',
                'fecha_baja',
            ])
            ->with([
                'tipoDocumento:id,nombre',
                'ciudad:id,nombre',
            ])
            ->when($filters['buscar'] !== '', function (Builder $query) use ($filters): void {
                $term = LikePattern::contains($filters['buscar']);
                $cuilt = preg_match('/^[\d\s.-]+$/', $filters['buscar']) === 1
                    ? (preg_replace('/\D/', '', $filters['buscar']) ?? '')
                    : '';

                $query->where(function (Builder $query) use ($term, $cuilt): void {
                    $query
                        ->whereRaw("legajo LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("apellido LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("nombre LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("nombre_elegido LIKE ? ESCAPE '!'", [$term])
                        ->orWhereRaw("numero_documento LIKE ? ESCAPE '!'", [$term]);

                    if ($cuilt !== '') {
                        $query->orWhereRaw("cuilt LIKE ? ESCAPE '!'", [LikePattern::contains($cuilt)]);
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

        $totalAlumnos = Alumno::query()->count();
        $alumnosFiltrados = (clone $query)->count();

        return Inertia::render('academico/alumnos/index', [
            'alumnos' => $query
                ->orderByDesc('activo')
                ->orderBy('apellido')
                ->orderBy('nombre')
                ->get(),
            'filters' => $filters,
            'resultados' => [
                'total' => $totalAlumnos,
                'filtrados' => $alumnosFiltrados,
            ],
            'provincias' => Provincia::query()
                ->select(['id', 'activo', 'nombre'])
                ->where(function (Builder $query): void {
                    $query
                        ->where('activo', true)
                        ->orWhereHas('ciudades.alumnos');
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
                        ->orWhereHas('alumnos');
                })
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Ciudad $ciudad) => [
                    'value' => $ciudad->id,
                    'label' => $ciudad->nombre.($ciudad->activo ? '' : ' (inactiva)'),
                    'provincia_id' => $ciudad->provincia_id,
                ]),
            'motivosBaja' => MotivoBaja::query()
                ->select(['id', 'nombre'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Alumno::class);

        return $this->formulario();
    }

    public function show(VerAlumnoRequest $request, Alumno $alumno): Response
    {
        Gate::authorize('view', $alumno);

        $alumno->load([
            'tipoDocumento:id,nombre',
            'ciudad:id,activo,provincia_id,nombre,codigo_postal',
            'ciudad.provincia:id,activo,nombre',
            'ciudadNacimiento:id,activo,provincia_id,nombre',
            'ciudadNacimiento.provincia:id,activo,nombre',
            'nacionalidad:id,activo,nombre',
            'grupoSanguineo:id,codigo,nombre',
            'motivoBaja:id,nombre',
            'alumnosResponsables' => fn (HasMany $query) => $query
                ->select([
                    'id',
                    'activo',
                    'alumno_id',
                    'responsable_id',
                    'vinculo_id',
                    'orden_contacto',
                    'fecha_desde',
                    'fecha_hasta',
                    'es_responsable_legal',
                    'es_responsable_pedagogico',
                    'es_responsable_economico',
                    'recibe_comunicaciones',
                    'recibe_informes_academicos',
                    'recibe_facturacion',
                    'es_contacto_principal',
                    'es_contacto_emergencia',
                    'convive_con_alumno',
                    'autorizado_retirar',
                    'habilitado_portal',
                ])
                ->orderByDesc('activo')
                ->orderBy('orden_contacto')
                ->orderBy('id'),
            'alumnosResponsables.tipoVinculo:id,nombre',
            'alumnosResponsables.responsable' => fn (BelongsTo $query) => $query
                ->select([
                    'id',
                    'activo',
                    'apellido',
                    'nombre',
                    'nombre_elegido',
                    'tipo_documento_id',
                    'numero_documento',
                    'email',
                    'telefono_e164',
                ]),
            'alumnosResponsables.responsable.tipoDocumento:id,nombre',
        ]);

        return Inertia::render('academico/alumnos/show', [
            'alumno' => $this->detallePayload($alumno),
            'seccion' => $request->seccion(),
            'documentacion' => fn () => $alumno->adjuntos()
                ->with('usuario:id,login')
                ->orderByDesc('activo')
                ->orderByDesc('fecha_registro')
                ->orderByDesc('adjuntos.id')
                ->get()
                ->map(fn (Adjunto $adjunto) => [
                    'id' => $adjunto->id,
                    'activo' => $adjunto->activo,
                    'nombre_original' => $adjunto->nombre_original,
                    'mime' => $adjunto->mime,
                    'tamano_bytes' => $adjunto->tamano_bytes,
                    'observaciones' => $adjunto->observaciones,
                    'fecha_registro' => $adjunto->fecha_registro->toIso8601String(),
                    'usuario' => $adjunto->usuario->login,
                ]),
            'configAdjuntos' => [
                'max_bytes' => config('adjuntos.max_kb') * 1024,
                'extensiones' => config('adjuntos.extensiones'),
            ],
            'motivosBaja' => MotivoBaja::query()
                ->select(['id', 'nombre'])
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
            'tiposVinculo' => TipoVinculo::query()
                ->select(['id', 'nombre'])
                ->orderBy('id')
                ->get(),
        ]);
    }

    public function store(AlumnoRequest $request): RedirectResponse
    {
        $alumno = DB::transaction(function () use ($request): Alumno {
            $alumno = Alumno::query()->create($request->validated());
            $alumno->refresh();

            $this->auditar('create', $alumno, null, $alumno->attributesToArray());

            return $alumno;
        });

        $this->toast('Alumno creado.');

        return to_route('academico.alumnos.edit', $alumno);
    }

    public function edit(Alumno $alumno): Response
    {
        Gate::authorize('update', $alumno);

        return $this->formulario($alumno);
    }

    public function update(AlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        DB::transaction(function () use ($request, $alumno): void {
            $anterior = $alumno->attributesToArray();
            $alumno->fill($request->validated());

            if (! $alumno->isDirty()) {
                return;
            }

            $alumno->save();
            $alumno->refresh();

            $this->auditar('update', $alumno, $anterior, $alumno->attributesToArray());
        });

        $this->toast('Alumno actualizado.');

        return back();
    }

    public function desactivar(DarBajaAlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        DB::transaction(function () use ($request, $alumno): void {
            if (! $alumno->activo) {
                return;
            }

            $anterior = $alumno->attributesToArray();
            $alumno->fill([
                ...$request->validated(),
                'activo' => false,
            ])->save();
            $alumno->refresh();

            $this->auditar('deactivate', $alumno, $anterior, $alumno->attributesToArray());
        });

        $this->toast('Alumno desactivado.');

        return back();
    }

    public function reactivar(CambiarEstadoAlumnoRequest $request, Alumno $alumno): RedirectResponse
    {
        DB::transaction(function () use ($alumno): void {
            if ($alumno->activo) {
                return;
            }

            $anterior = $alumno->attributesToArray();
            $alumno->fill([
                'activo' => true,
                'fecha_baja' => null,
                'motivo_baja_id' => null,
            ])->save();
            $alumno->refresh();

            $this->auditar('reactivate', $alumno, $anterior, $alumno->attributesToArray());
        });

        $this->toast('Alumno reactivado.');

        return back();
    }

    private function formulario(?Alumno $alumno = null): Response
    {
        $ciudadesActuales = collect([
            $alumno?->ciudad_id,
            $alumno?->ciudad_nacimiento_id,
        ])->filter()->values();

        return Inertia::render('academico/alumnos/formulario', [
            'alumno' => $alumno === null
                ? null
                : [
                    ...$alumno->toArray(),
                    'fecha_nacimiento' => $alumno->fecha_nacimiento?->format('Y-m-d'),
                    'fecha_ingreso' => $alumno->fecha_ingreso?->format('Y-m-d'),
                    'fecha_inicio_cursado' => $alumno->fecha_inicio_cursado?->format('Y-m-d'),
                ],
            'tiposDocumento' => TipoDocumento::query()
                ->select(['id', 'nombre'])
                ->orderBy('nombre')
                ->get(),
            'ciudades' => Ciudad::query()
                ->select(['id', 'activo', 'provincia_id', 'nombre', 'codigo_postal'])
                ->with('provincia:id,activo,nombre')
                ->where(function (Builder $consulta) use ($ciudadesActuales): void {
                    $consulta->where('activo', true);

                    if ($ciudadesActuales->isNotEmpty()) {
                        $consulta->orWhereIn('id', $ciudadesActuales);
                    }
                })
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get(),
            'nacionalidades' => Nacionalidad::query()
                ->select(['id', 'activo', 'nombre'])
                ->where('activo', true)
                ->when(
                    $alumno?->nacionalidad_id,
                    fn (Builder $consulta, int $id) => $consulta->orWhere('id', $id),
                )
                ->orderByDesc('activo')
                ->orderBy('nombre')
                ->get(),
            'gruposSanguineos' => GrupoSanguineo::query()
                ->select(['id', 'codigo', 'nombre'])
                ->orderBy('codigo')
                ->get(),
        ]);
    }

    /** @return array<string, mixed> */
    private function detallePayload(Alumno $alumno): array
    {
        return [
            'id' => $alumno->id,
            'activo' => $alumno->activo,
            'legajo' => $alumno->legajo,
            'apellido' => $alumno->apellido,
            'nombre' => $alumno->nombre,
            'nombre_elegido' => $alumno->nombre_elegido,
            'tipo_documento' => $alumno->tipoDocumento?->nombre,
            'numero_documento' => $alumno->numero_documento,
            'cuilt' => $alumno->cuilt,
            'fecha_nacimiento' => $alumno->fecha_nacimiento?->format('Y-m-d'),
            'ciudad_nacimiento' => $alumno->ciudadNacimiento?->nombre,
            'provincia_nacimiento' => $alumno->ciudadNacimiento?->provincia?->nombre,
            'nacionalidad' => $alumno->nacionalidad?->nombre,
            'grupo_sanguineo' => $alumno->grupoSanguineo?->codigo,
            'sexo_registral' => $alumno->sexo_registral,
            'genero' => $alumno->genero,
            'genero_autodescripcion' => $alumno->genero_autodescripcion,
            'email' => $alumno->email,
            'domicilio' => $alumno->domicilio,
            'ciudad' => $alumno->ciudad?->nombre,
            'provincia' => $alumno->ciudad?->provincia?->nombre,
            'cpa' => $alumno->cpa,
            'fecha_ingreso' => $alumno->fecha_ingreso?->format('Y-m-d'),
            'fecha_inicio_cursado' => $alumno->fecha_inicio_cursado?->format('Y-m-d'),
            'libro' => $alumno->libro,
            'folio' => $alumno->folio,
            'fecha_baja' => $alumno->fecha_baja?->format('Y-m-d'),
            'motivo_baja' => $alumno->motivoBaja?->nombre,
            'autoriza_uso_imagen' => $alumno->autoriza_uso_imagen,
            'observaciones' => $alumno->observaciones,
            'responsables' => $alumno->alumnosResponsables
                ->map(fn (AlumnoResponsable $vinculo) => [
                    'id' => $vinculo->responsable->id,
                    'alumno_responsable_id' => $vinculo->id,
                    'activo' => $vinculo->responsable->activo,
                    'vinculo_activo' => $vinculo->activo,
                    'apellido' => $vinculo->responsable->apellido,
                    'nombre' => $vinculo->responsable->nombre,
                    'nombre_elegido' => $vinculo->responsable->nombre_elegido,
                    'tipo_documento' => $vinculo->responsable->tipoDocumento?->nombre,
                    'numero_documento' => $vinculo->responsable->numero_documento,
                    'email' => $vinculo->responsable->email,
                    'telefono' => $vinculo->responsable->telefono_e164,
                    'tipo_vinculo' => $vinculo->tipoVinculo->nombre,
                    'tipo_vinculo_id' => $vinculo->vinculo_id,
                    'orden_contacto' => $vinculo->orden_contacto,
                    'fecha_desde' => $vinculo->fecha_desde?->format('Y-m-d'),
                    'fecha_hasta' => $vinculo->fecha_hasta?->format('Y-m-d'),
                    'es_responsable_legal' => $vinculo->es_responsable_legal,
                    'es_responsable_pedagogico' => $vinculo->es_responsable_pedagogico,
                    'es_responsable_economico' => $vinculo->es_responsable_economico,
                    'recibe_comunicaciones' => $vinculo->recibe_comunicaciones,
                    'recibe_informes_academicos' => $vinculo->recibe_informes_academicos,
                    'recibe_facturacion' => $vinculo->recibe_facturacion,
                    'es_contacto_principal' => $vinculo->es_contacto_principal,
                    'es_contacto_emergencia' => $vinculo->es_contacto_emergencia,
                    'convive_con_alumno' => $vinculo->convive_con_alumno,
                    'autorizado_retirar' => $vinculo->autorizado_retirar,
                    'habilitado_portal' => $vinculo->habilitado_portal,
                    'observaciones' => $vinculo->observaciones,
                    'funciones' => $this->funcionesResponsable($vinculo),
                ])
                ->values(),
        ];
    }

    /** @return array<int, string> */
    private function funcionesResponsable(AlumnoResponsable $vinculo): array
    {
        return collect([
            'Responsable legal' => $vinculo->es_responsable_legal,
            'Responsable pedagógico' => $vinculo->es_responsable_pedagogico,
            'Responsable económico' => $vinculo->es_responsable_economico,
            'Recibe comunicaciones' => $vinculo->recibe_comunicaciones,
            'Recibe informes académicos' => $vinculo->recibe_informes_academicos,
            'Recibe facturación' => $vinculo->recibe_facturacion,
            'Contacto principal' => $vinculo->es_contacto_principal,
            'Contacto de emergencia' => $vinculo->es_contacto_emergencia,
            'Convive con el alumno' => $vinculo->convive_con_alumno,
            'Autorizado a retirar' => $vinculo->autorizado_retirar,
            'Acceso al portal' => $vinculo->habilitado_portal,
        ])
            ->filter()
            ->keys()
            ->values()
            ->all();
    }

    private function toast(string $mensaje): void
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $mensaje]);
    }
}
