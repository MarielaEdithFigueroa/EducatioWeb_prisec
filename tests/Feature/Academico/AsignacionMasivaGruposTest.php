<?php

use App\Actions\Academico\GestionarGrupoAlumno;
use App\Models\Alumno;
use App\Models\AlumnoGrupo;
use App\Models\Grupo;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    request()->setLaravelSession(app('session.store'));
    $this->usuario = User::factory()->create();
    $this->destino = Grupo::factory()->create();
    $this->primero = Alumno::query()->create(['legajo' => 'MAS-1', 'apellido' => 'Alvarez', 'nombre' => 'Ana']);
    $this->segundo = Alumno::query()->create(['legajo' => 'MAS-2', 'apellido' => 'Benitez', 'nombre' => 'Bruno']);
});

function seleccionMasiva(Alumno ...$alumnos): array
{
    return array_map(fn (Alumno $alumno): array => [
        'alumno_id' => $alumno->id,
        'version_historial' => AlumnoGrupo::version($alumno->historialGrupos()->orderByDesc('id')->first()),
    ], $alumnos);
}

it('requiere autenticación tanto para seleccionar como para asignar masivamente', function () {
    $this->get(route('academico.grupos.alumnos.create', $this->destino))->assertRedirect(route('login'));
    $this->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => seleccionMasiva($this->primero), 'motivo' => 'Ingreso',
    ])->assertRedirect(route('login'));
    expect(AlumnoGrupo::query()->count())->toBe(0);
});

it('muestra candidatos activos con el grupo y la versión del mismo historial sin exponer auditoría', function () {
    $this->actingAs($this->usuario);
    $this->primero->update(['fecha_nacimiento' => '2015-12-31']);
    $gestionar = app(GestionarGrupoAlumno::class);
    $gestionar->cambiar($this->primero, $this->destino->id, '0', 'Ingreso');
    $gestionar->cambiar($this->segundo, $this->destino->id, '0', 'Ingreso');
    $version = seleccionMasiva($this->segundo)[0]['version_historial'];
    $gestionar->cambiar($this->segundo, null, $version, 'Egreso');
    Alumno::query()->create(['legajo' => 'BAJA', 'apellido' => 'Baja', 'nombre' => 'Baja', 'activo' => false]);

    $this->get(route('academico.grupos.alumnos.create', $this->destino))->assertInertia(fn (Assert $page) => $page
        ->component('academico/grupos/asignar-alumnos')->has('alumnos', 2)
        ->where('alumnos.0.id', $this->segundo->id)->where('alumnos.0.grupo_id', null)
        ->where('alumnos.0.cohorte_nacimiento', null)->where('alumnos.0.ciclo_lectivo_actual', null)
        ->where('alumnos.0.version_historial', seleccionMasiva($this->segundo)[0]['version_historial'])
        ->where('alumnos.1.grupo_id', $this->destino->id)
        ->where('alumnos.1.cohorte_nacimiento', 2016)->where('alumnos.1.ciclo_lectivo_actual', $this->destino->ciclo_lectivo)
        ->where('alumnos.1.grupo_descripcion', $this->destino->descripcion)
        ->where('alumnos.1.version_historial', seleccionMasiva($this->primero)[0]['version_historial'])
        ->missing('alumnos.1.session_id')->missing('alumnos.1.auditoria'));
});

it('agrupa los nacimientos por el corte escolar del 30 de junio sin modificar la fecha personal', function (?string $fecha, ?int $cohorte) {
    $this->primero->update(['fecha_nacimiento' => $fecha]);
    $this->actingAs($this->usuario)->get(route('academico.grupos.alumnos.create', $this->destino))
        ->assertInertia(fn (Assert $page) => $page
            ->where('alumnos.1.id', $this->primero->id)
            ->where('alumnos.1.cohorte_nacimiento', $cohorte)
            ->missing('alumnos.1.fecha_nacimiento'));

    expect($this->primero->fresh()->fecha_nacimiento?->toDateString())->toBe($fecha);
})->with([
    'fin de cohorte anterior' => ['2019-06-30', 2019],
    'inicio de cohorte' => ['2019-07-01', 2020],
    'fin de año calendario' => ['2019-12-31', 2020],
    'inicio de año calendario' => ['2020-01-01', 2020],
    'año bisiesto' => ['2020-02-29', 2020],
    'día de corte incluido' => ['2020-06-30', 2020],
    'día siguiente al corte' => ['2020-07-01', 2021],
    'fecha desconocida' => [null, null],
]);

it('asigna un lote mixto de ingresos y traslados conservando toda la auditoría', function () {
    $this->actingAs($this->usuario)->withSession(['lote' => true])->withHeader('User-Agent', 'Prueba masiva');
    $origen = Grupo::factory()->create();
    app(GestionarGrupoAlumno::class)->cambiar($this->segundo, $origen->id, '0', 'Origen');
    $logsAntes = Log::query()->count();
    $this->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => seleccionMasiva($this->segundo, $this->primero), 'motivo' => 'Organización de grupos',
        'session_id' => 'falsa', 'usuario_id' => 999,
    ])->assertSessionHasNoErrors()->assertRedirect(route('academico.grupos.show', $this->destino));

    foreach ([$this->primero, $this->segundo] as $alumno) {
        expect($alumno->fresh()->grupo_id)->toBe($this->destino->id)
            ->and($alumno->asignacionActual()->sole()->grupo_id)->toBe($this->destino->id)
            ->and($alumno->asignacionActual()->sole()->motivo_ingreso)->toBe('Organización de grupos');
    }
    $historial = $this->segundo->historialGrupos()->orderBy('id')->get();
    expect($historial)->toHaveCount(2)->and($historial[0]->fecha_hasta->equalTo($historial[1]->fecha_desde))->toBeTrue();
    $logs = Log::query()->orderBy('id')->get()->skip($logsAntes);
    expect($logs)->toHaveCount(5)->and($logs->pluck('session_id')->unique())->toHaveCount(1);
    foreach ($logs as $log) {
        expect($log->usuario_id)->toBe($this->usuario->id)->and($log->login)->toBe($this->usuario->login)
            ->and($log->session_id)->not->toBeEmpty()->not->toBe('falsa')->and($log->created_at)->not->toBeNull()
            ->and($log->ip)->not->toBeEmpty()->and($log->user_agent)->toBe('Prueba masiva')->and($log->origen)->toBe('web');
    }
});

it('omite los que ya pertenecen al grupo sin duplicar historial ni logs', function () {
    $this->actingAs($this->usuario);
    app(GestionarGrupoAlumno::class)->cambiar($this->primero, $this->destino->id, '0', 'Ingreso');
    $this->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => seleccionMasiva($this->primero, $this->segundo), 'motivo' => 'Completar grupo',
    ])->assertSessionHasNoErrors();
    expect(AlumnoGrupo::query()->count())->toBe(2)->and(Log::query()->count())->toBe(4);
});

it('revierte todo el lote cuando el segundo alumno tiene una versión obsoleta', function () {
    $this->actingAs($this->usuario);
    $seleccion = seleccionMasiva($this->primero, $this->segundo);
    $origen = Grupo::factory()->create();
    app(GestionarGrupoAlumno::class)->cambiar($this->segundo, $origen->id, '0', 'Cambio simultáneo');
    $this->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => $seleccion, 'motivo' => 'Completar grupo',
    ])->assertSessionHasErrors('alumnos');
    expect($this->primero->fresh()->grupo_id)->toBeNull()->and($this->segundo->fresh()->grupo_id)->toBe($origen->id)
        ->and(AlumnoGrupo::query()->count())->toBe(1)->and(Log::query()->count())->toBe(2);
});

it('valida alumnos motivos y versiones de la selección', function (string $caso, string $campo) {
    $datos = ['alumnos' => seleccionMasiva($this->primero), 'motivo' => 'Ingreso'];
    match ($caso) {
        'vacio' => $datos['alumnos'] = [],
        'duplicado' => $datos['alumnos'][] = $datos['alumnos'][0],
        'inexistente' => $datos['alumnos'][0]['alumno_id'] = 999999,
        'version' => $datos['alumnos'][0]['version_historial'] = '',
        'motivo' => $datos['motivo'] = '',
        'motivo_largo' => $datos['motivo'] = str_repeat('a', 501),
        'exceso' => $datos['alumnos'] = array_fill(0, 201, $datos['alumnos'][0]),
        'inactivo' => $this->primero->update(['activo' => false]),
    };
    $this->actingAs($this->usuario)->post(route('academico.grupos.alumnos.store', $this->destino), $datos)->assertSessionHasErrors($campo);
    expect(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
})->with([
    ['vacio', 'alumnos'], ['duplicado', 'alumnos.0.alumno_id'], ['inexistente', 'alumnos.0.alumno_id'],
    ['version', 'alumnos.0.version_historial'], ['motivo', 'motivo'], ['motivo_largo', 'motivo'],
    ['exceso', 'alumnos'], ['inactivo', 'alumnos.0.alumno_id'],
]);

it('rechaza un destino inactivo en el formulario y en la operación bajo bloqueo', function () {
    $this->actingAs($this->usuario);
    $seleccion = seleccionMasiva($this->primero);
    $this->destino->update(['activo' => false]);
    $this->post(route('academico.grupos.alumnos.store', $this->destino), ['alumnos' => $seleccion, 'motivo' => 'Ingreso'])->assertSessionHasErrors('alumnos');
    expect(fn () => app(GestionarGrupoAlumno::class)->asignarMasivamente($this->destino, $seleccion, 'Ingreso'))->toThrow(ValidationException::class);
    expect(AlumnoGrupo::query()->count())->toBe(0);
});

it('revalida el estado del alumno bajo bloqueo y revierte los movimientos previos', function () {
    $this->actingAs($this->usuario);
    $seleccion = seleccionMasiva($this->primero, $this->segundo);
    $this->segundo->update(['activo' => false]);
    expect(fn () => app(GestionarGrupoAlumno::class)->asignarMasivamente($this->destino, $seleccion, 'Ingreso'))->toThrow(ValidationException::class);
    expect($this->primero->fresh()->grupo_id)->toBeNull()->and(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});

it('revierte todos los alumnos si falla la auditoría del segundo', function () {
    $this->actingAs($this->usuario)->withoutExceptionHandling();
    $segundoId = $this->segundo->id;
    Log::creating(function (Log $log) use ($segundoId): void {
        if ($log->entidad === 'alumnos' && $log->entidad_id === $segundoId) {
            throw new RuntimeException('Fallo de auditoría masiva');
        }
    });
    try {
        expect(fn () => $this->post(route('academico.grupos.alumnos.store', $this->destino), [
            'alumnos' => seleccionMasiva($this->primero, $this->segundo), 'motivo' => 'Ingreso',
        ]))->toThrow(RuntimeException::class, 'Fallo de auditoría masiva');
    } finally {
        Log::flushEventListeners();
    }
    expect($this->primero->fresh()->grupo_id)->toBeNull()->and($this->segundo->fresh()->grupo_id)->toBeNull()
        ->and(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});

it('cancela el lote ante una inconsistencia del segundo alumno', function () {
    DB::table('alumnos')->where('id', $this->segundo->id)->update(['grupo_id' => $this->destino->id]);
    $this->actingAs($this->usuario)->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => seleccionMasiva($this->primero, $this->segundo), 'motivo' => 'Ingreso',
    ])->assertSessionHasErrors('alumnos');
    expect($this->primero->fresh()->grupo_id)->toBeNull()->and(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});

it('autoriza también cada alumno y revierte el lote ante un permiso denegado', function () {
    $segundoId = $this->segundo->id;
    Gate::before(fn (User $usuario, string $habilidad, array $argumentos): ?bool => $habilidad === 'update' && ($argumentos[0] ?? null) instanceof Alumno && $argumentos[0]->id === $segundoId ? false : null);
    $this->actingAs($this->usuario)->post(route('academico.grupos.alumnos.store', $this->destino), [
        'alumnos' => seleccionMasiva($this->primero, $this->segundo), 'motivo' => 'Ingreso',
    ])->assertForbidden();
    expect($this->primero->fresh()->grupo_id)->toBeNull()->and(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});
