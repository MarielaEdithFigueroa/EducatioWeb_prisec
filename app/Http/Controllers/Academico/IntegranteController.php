<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GuardarIntegrante;
use App\Http\Requests\Academico\CambiarEstadoCatalogoRequest;
use App\Http\Requests\Academico\DarBajaIntegranteRequest;
use App\Http\Requests\Academico\IndexIntegranteRequest;
use App\Http\Requests\Academico\IntegranteRequest;
use App\Models\Adjunto;
use App\Models\Ciudad;
use App\Models\ContactoIntegrante;
use App\Models\FuncionEquipo;
use App\Models\Integrante;
use App\Models\Nacionalidad;
use App\Models\TipoDocumentacionIntegrante;
use App\Models\TipoDocumento;
use App\Services\TelefonoNormalizer;
use App\Support\Database\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class IntegranteController extends CatalogoController
{
    private const RELACIONES = ['tipoDocumento', 'ciudad.provincia', 'ciudadNacimiento.provincia', 'nacionalidad', 'funciones'];

    public function index(IndexIntegranteRequest $request): Response
    {
        $filters = $request->filters();
        $query = Integrante::query()->select(['id', 'activo', 'legajo', 'apellido', 'nombre', 'tipo_documento_id', 'numero_documento', 'cuilt', 'fecha_ingreso', 'fecha_baja'])
            ->with(['funciones', 'tipoDocumento:id,nombre']);
        if ($filters['buscar'] !== '') {
            $texto = trim($filters['buscar']);
            $query->where(function (Builder $query) use ($texto): void {
                $query->where(function (Builder $query) use ($texto): void {
                    foreach (preg_split('/\\s+/', $texto, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
                        $query->where(function (Builder $query) use ($palabra): void {
                            foreach (['apellido', 'nombre', 'legajo', 'numero_documento', 'cuilt'] as $campo) {
                                $query->orWhereRaw($campo." LIKE ? ESCAPE '!'", [LikePattern::contains($palabra)]);
                            }
                        });
                    }
                });
                if (preg_match('/^[0-9 .-]+$/', $texto)) {
                    $digitos = preg_replace('/\\D/', '', $texto);
                    if ($digitos !== '') {
                        $query->orWhereRaw("cuilt LIKE ? ESCAPE '!'", [LikePattern::contains($digitos)])
                            ->orWhereRaw("numero_documento LIKE ? ESCAPE '!'", [LikePattern::contains($digitos)]);
                    }
                }
            });
        }
        if ($filters['estado'] !== '') {
            $query->where('activo', $filters['estado'] === 'activos');
        }
        if ($filters['funcion_id'] !== '') {
            $query->whereHas('funciones', fn (Builder $query) => $query->where('funciones_equipo.id', $filters['funcion_id']));
        }
        $integrantes = $query->orderBy('apellido')->orderBy('nombre')->orderBy('legajo')->get();

        return Inertia::render('academico/integrantes/index', [
            'integrantes' => $integrantes, 'filters' => $filters,
            'funcionesEquipo' => FuncionEquipo::query()->orderBy('orden')->get(),
            'resultados' => ['total' => Integrante::query()->count(), 'filtrados' => $integrantes->count()],
        ]);
    }

    public function create(IndexIntegranteRequest $request): Response
    {
        Gate::authorize('create', Integrante::class);

        return $this->formulario($request);
    }

    public function edit(IndexIntegranteRequest $request, Integrante $integrante): Response|RedirectResponse
    {
        Gate::authorize('update', $integrante);
        if (! $integrante->activo) {
            return to_route('academico.integrantes.show', ['integrante' => $integrante->id, 'filtros' => $request->filters(), 'seccion' => $request->seccion()]);
        }

        return $this->formulario($request, $integrante);
    }

    public function show(IndexIntegranteRequest $request, Integrante $integrante): Response
    {
        Gate::authorize('view', $integrante);

        return Inertia::render('academico/integrantes/show', [
            'integrante' => $this->detalle($integrante),
            'documentacion' => $this->documentacion($integrante),
            'filters' => $request->filters(),
            'seccion' => $request->seccion(),
            'adjuntosLibres' => $this->adjuntosLibres($integrante),
            'configAdjuntos' => ['extensiones' => config('adjuntos.extensiones'), 'max_bytes' => config('adjuntos.max_kb') * 1024],
            'configPdf' => ['extensiones' => ['pdf'], 'max_bytes' => config('adjuntos.max_kb') * 1024],
        ]);
    }

    public function store(IntegranteRequest $request, GuardarIntegrante $guardar): RedirectResponse
    {
        $integrante = $guardar->ejecutar(new Integrante, $request->validated(), $request->file('foto'));
        $this->toast('Integrante incorporado.');

        return $this->volverGuardado($request, $integrante);
    }

    public function imprimir(IndexIntegranteRequest $request, Integrante $integrante): Response
    {
        Gate::authorize('view', $integrante);

        return Inertia::render('academico/integrantes/imprimir', [
            'integrante' => $this->detalle($integrante),
            'documentacion' => $this->documentacion($integrante),
            'filters' => $request->filters(),
            'seccion' => $request->seccion(),
        ]);
    }

    public function update(IntegranteRequest $request, Integrante $integrante, GuardarIntegrante $guardar): RedirectResponse
    {
        $guardar->ejecutar($integrante, $request->validated(), $request->file('foto'));
        $this->toast('Legajo actualizado.');

        return $this->volverGuardado($request, $integrante);
    }

    public function desactivar(DarBajaIntegranteRequest $request, Integrante $integrante): RedirectResponse
    {
        DB::transaction(function () use ($request, $integrante): void {
            $integrante = Integrante::query()->lockForUpdate()->findOrFail($integrante->id);
            if (! $integrante->activo) {
                return;
            }
            $anterior = $integrante->attributesToArray();
            $integrante->fill([...$request->validated(), 'activo' => false])->save();
            $this->auditar('deactivate', $integrante, $anterior, $integrante->attributesToArray());
        });
        $this->toast('Integrante dado de baja. Su legajo permanece disponible para consulta.');

        return back();
    }

    public function reactivar(CambiarEstadoCatalogoRequest $request, Integrante $integrante): RedirectResponse
    {
        DB::transaction(function () use ($integrante): void {
            $integrante = Integrante::query()->lockForUpdate()->findOrFail($integrante->id);
            if ($integrante->activo) {
                return;
            }
            $anterior = $integrante->attributesToArray();
            $integrante->fill(['activo' => true, 'fecha_baja' => null, 'motivo_baja' => null])->save();
            $this->auditar('reactivate', $integrante, $anterior, $integrante->attributesToArray());
        });
        $this->toast('Integrante reincorporado.');

        return back();
    }

    public function foto(Integrante $integrante): StreamedResponse
    {
        Gate::authorize('view', $integrante);
        $disco = Storage::disk('adjuntos');
        abort_unless($integrante->foto_ruta && $disco->exists($integrante->foto_ruta), 404);
        $mime = $disco->mimeType($integrante->foto_ruta);
        abort_unless(in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

        return $disco->response($integrante->foto_ruta, 'foto-integrante', [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }

    private function volverGuardado(IntegranteRequest $request, Integrante $integrante): RedirectResponse
    {
        return to_route($request->boolean('continuar') ? 'academico.integrantes.edit' : 'academico.integrantes.show', [
            'integrante' => $integrante->id,
            ...($request->validated('seccion') ? ['seccion' => $request->validated('seccion')] : []),
            'filtros' => array_map(fn (mixed $valor): string => (string) ($valor ?? ''), $request->validated('filtros', [])),
        ]);
    }

    /** @return array<string, mixed> */
    private function detalle(Integrante $integrante): array
    {
        $tiposTelefono = [];
        foreach (Integrante::TELEFONOS_CON_TIPO as $campo) {
            $tiposTelefono[$campo.'_es_fijo'] = TelefonoNormalizer::esLineaFija($integrante->{$campo});
        }

        $integrante->load([...self::RELACIONES,
            'referenciasLaborales' => fn ($query) => $query->where('activo', true)->orderBy('id'),
            'contactos' => fn ($query) => $query->where('activo', true)->orderBy('id'),
        ]);

        return [...$integrante->toArray(), ...$tiposTelefono, 'tiene_foto' => $integrante->foto_ruta !== null,
            'referencias_laborales' => $this->filasRelacionadas($integrante->referenciasLaborales, ['referencia_1_telefono', 'referencia_2_telefono']),
            'contactos' => $this->filasRelacionadas($integrante->contactos, ['telefono']),
        ];
    }

    /** @param Collection<int, Model> $registros
     * @param  list<string>  $telefonos
     * @return list<array<string, mixed>>
     */
    private function filasRelacionadas(Collection $registros, array $telefonos): array
    {
        return $registros->map(function (Model $registro) use ($telefonos): array {
            $datos = $registro->attributesToArray();
            unset($datos['activo'], $datos['integrante_id']);
            $datos = array_map(fn (mixed $valor): mixed => $valor ?? '', $datos);
            foreach ($telefonos as $telefono) {
                $datos[$telefono.'_es_fijo'] = TelefonoNormalizer::esLineaFija($registro->{$telefono});
            }

            return $datos;
        })->all();
    }

    /** @return list<array<string, mixed>> */
    private function documentacion(?Integrante $integrante): array
    {
        $presentaciones = $integrante?->documentacion()->with(['adjuntos' => fn ($query) => $query->where('activo', true)])->get()->keyBy('tipo_documentacion_integrante_id') ?? collect();

        return TipoDocumentacionIntegrante::query()->where('activo', true)->orWhereIn('id', $presentaciones->keys())
            ->orderByDesc('activo')->orderBy('descripcion')->get()
            ->map(fn (TipoDocumentacionIntegrante $tipo): array => [
                'tipo_documentacion_integrante_id' => $tipo->id,
                'descripcion' => $tipo->descripcion,
                'activo' => $tipo->activo,
                'presentado' => $presentaciones->get($tipo->id)?->presentado ?? false,
                'adjunto' => $presentaciones->get($tipo->id)?->adjuntos->first()?->only(['id', 'nombre_original', 'tamano_bytes']),
            ])->all();
    }

    /** @return list<array<string, mixed>> */
    private function adjuntosLibres(?Integrante $integrante): array
    {
        return $integrante?->adjuntos()->with('usuario:id,login')
            ->orderByDesc('activo')->orderByDesc('fecha_registro')->orderByDesc('adjuntos.id')
            ->get()->map(fn (Adjunto $adjunto): array => [
                ...$adjunto->only(['id', 'activo', 'nombre_original', 'mime', 'tamano_bytes', 'observaciones']),
                'fecha_registro' => $adjunto->fecha_registro->toIso8601String(),
                'usuario' => $adjunto->usuario->login,
            ])->all() ?? [];
    }

    private function formulario(IndexIntegranteRequest $request, ?Integrante $integrante = null): Response
    {
        $idsCiudades = array_filter([$integrante?->ciudad_id, $integrante?->ciudad_nacimiento_id]);

        return Inertia::render('academico/integrantes/formulario', [
            'integrante' => $integrante ? $this->detalle($integrante) : null,
            'documentacion' => $this->documentacion($integrante),
            'filters' => $request->filters(),
            'seccion' => $request->seccion(),
            'catalogos' => [
                'estados_civiles' => Integrante::ESTADOS_CIVILES,
                'vinculos_contacto' => ContactoIntegrante::VINCULOS,
                'funciones_equipo' => FuncionEquipo::query()->orderBy('orden')->get(),
                'tipos_documento' => TipoDocumento::query()->orderBy('nombre')->get(['id', 'nombre']),
                'ciudades' => Ciudad::query()->with('provincia:id,activo,nombre')
                    ->where('activo', true)->orWhereIn('id', $idsCiudades)->orderBy('nombre')
                    ->get(['id', 'activo', 'nombre', 'provincia_id', 'codigo_postal']),
                'nacionalidades' => Nacionalidad::query()->where('activo', true)->orWhere('id', $integrante?->nacionalidad_id)
                    ->orderBy('nombre')->get(['id', 'activo', 'nombre']),
            ],
            'configFoto' => ['extensiones' => config('adjuntos.extensiones_fotos'), 'max_bytes' => config('adjuntos.max_kb') * 1024],
            'adjuntosLibres' => $this->adjuntosLibres($integrante),
            'configAdjuntos' => ['extensiones' => config('adjuntos.extensiones'), 'max_bytes' => config('adjuntos.max_kb') * 1024],
            'configPdf' => ['extensiones' => ['pdf'], 'max_bytes' => config('adjuntos.max_kb') * 1024],
        ]);
    }
}
