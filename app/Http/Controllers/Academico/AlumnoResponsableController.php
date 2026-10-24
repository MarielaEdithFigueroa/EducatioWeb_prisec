<?php

namespace App\Http\Controllers\Academico;

use App\Actions\Academico\GestionarResponsableAlumno;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academico\BuscarResponsableRequest;
use App\Http\Requests\Academico\VincularResponsableRequest;
use App\Models\Alumno;
use App\Models\AlumnoResponsable;
use App\Models\Responsable;
use App\Support\Database\LikePattern;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class AlumnoResponsableController extends Controller
{
    public function buscar(BuscarResponsableRequest $request, Alumno $alumno): JsonResponse
    {
        $termino = $request->termino();
        $patron = LikePattern::contains($termino);
        $digitos = preg_match('/^[\d\s()+.-]+$/', $termino) === 1
            ? (preg_replace('/\D+/', '', $termino) ?? '')
            : '';

        $responsables = Responsable::query()
            ->select([
                'id', 'activo', 'apellido', 'nombre', 'nombre_elegido',
                'tipo_documento_id', 'numero_documento', 'cuilt', 'email', 'telefono_e164',
            ])
            ->with([
                'tipoDocumento:id,nombre',
                'alumnosResponsables' => fn (HasMany $consulta) => $consulta
                    ->select([
                        'id', 'activo', 'alumno_id', 'responsable_id', 'vinculo_id', 'orden_contacto',
                        'fecha_desde', 'fecha_hasta', 'es_responsable_legal',
                        'es_responsable_pedagogico', 'es_responsable_economico',
                        'recibe_comunicaciones', 'recibe_informes_academicos', 'recibe_facturacion',
                        'es_contacto_principal', 'es_contacto_emergencia', 'convive_con_alumno',
                        'autorizado_retirar', 'habilitado_portal', 'observaciones',
                    ]),
                'alumnosResponsables.alumno:id,activo,legajo,apellido,nombre',
            ])
            ->where(function (Builder $consulta) use ($patron, $digitos): void {
                $consulta
                    ->whereRaw("apellido LIKE ? ESCAPE '!'", [$patron])
                    ->orWhereRaw("nombre LIKE ? ESCAPE '!'", [$patron])
                    ->orWhereRaw("nombre_elegido LIKE ? ESCAPE '!'", [$patron])
                    ->orWhereRaw("numero_documento LIKE ? ESCAPE '!'", [$patron])
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$patron]);

                if ($digitos !== '') {
                    $patronDigitos = LikePattern::contains($digitos);
                    $consulta
                        ->orWhereRaw("cuilt LIKE ? ESCAPE '!'", [$patronDigitos])
                        ->orWhereRaw("telefono_e164 LIKE ? ESCAPE '!'", [$patronDigitos]);
                }
            })
            ->orderByDesc('activo')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->limit(25)
            ->get()
            ->map(function (Responsable $responsable) use ($alumno): array {
                $vinculoActual = $responsable->alumnosResponsables
                    ->firstWhere('alumno_id', $alumno->id);

                return [
                    'id' => $responsable->id,
                    'activo' => $responsable->activo,
                    'apellido' => $responsable->apellido,
                    'nombre' => $responsable->nombre,
                    'nombre_elegido' => $responsable->nombre_elegido,
                    'tipo_documento' => $responsable->tipoDocumento?->nombre,
                    'numero_documento' => $responsable->numero_documento,
                    'cuilt' => $responsable->cuilt,
                    'email' => $responsable->email,
                    'telefono' => $responsable->telefono_e164,
                    'vinculo' => $vinculoActual instanceof AlumnoResponsable
                        ? $this->vinculoPayload($vinculoActual)
                        : null,
                    'otros_alumnos' => $responsable->alumnosResponsables
                        ->where('activo', true)
                        ->where('alumno_id', '!=', $alumno->id)
                        ->take(3)
                        ->map(fn (AlumnoResponsable $vinculo) => [
                            'id' => $vinculo->alumno->id,
                            'nombre' => $vinculo->alumno->apellido.', '.$vinculo->alumno->nombre,
                            'legajo' => $vinculo->alumno->legajo,
                        ])
                        ->values(),
                ];
            });

        return response()->json(['responsables' => $responsables]);
    }

    public function store(
        VincularResponsableRequest $request,
        Alumno $alumno,
        GestionarResponsableAlumno $gestionar,
    ): RedirectResponse {
        $responsable = Responsable::query()->findOrFail($request->integer('responsable_id'));
        $gestionar->vincular($alumno, $responsable, $request->datosVinculo());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Responsable vinculado.']);

        return to_route('academico.alumnos.show', [
            'alumno' => $alumno,
            'seccion' => 'responsables',
        ]);
    }

    /** @return array<string, mixed> */
    private function vinculoPayload(AlumnoResponsable $vinculo): array
    {
        return [
            'id' => $vinculo->id,
            'activo' => $vinculo->activo,
            'vinculo_id' => $vinculo->vinculo_id,
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
        ];
    }
}
