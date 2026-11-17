<?php

use App\Models\Alumno;
use App\Models\AlumnoGrupo;
use App\Models\Grupo;
use App\Models\Log;
use App\Models\MotivoBaja;
use App\Models\PlanEstudio;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->alumno = Alumno::query()->create(['legajo' => 'GRUPOS-1', 'apellido' => 'Pérez', 'nombre' => 'Ana']);
    $this->grupo = Grupo::factory()->create();
    $this->usuario = User::factory()->create();
});

function datosAsignacion(Alumno $alumno, ?Grupo $grupo, string $motivo = 'Cambio solicitado'): array
{
    return [
        'grupo_id' => $grupo?->id,
        'version_historial' => AlumnoGrupo::version($alumno->historialGrupos()->orderByDesc('id')->first()),
        'motivo' => $motivo,
    ];
}

function asignarGrupoPrueba(Alumno $alumno, ?Grupo $grupo, string $motivo = 'Cambio solicitado'): void
{
    test()->patch(route('academico.alumnos.grupo.update', $alumno), datosAsignacion($alumno, $grupo, $motivo))
        ->assertSessionHasNoErrors()->assertRedirect(route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'grupos']));
}

it('requiere autenticación para cambiar la asignación', function () {
    $this->patch(route('academico.alumnos.grupo.update', $this->alumno), datosAsignacion($this->alumno, $this->grupo))
        ->assertRedirect(route('login'));
    expect(AlumnoGrupo::query()->count())->toBe(0)->and($this->alumno->fresh()->grupo_id)->toBeNull();
});

it('guarda ambas representaciones y audita con identidad hora sesión y petición del servidor', function () {
    $this->travelTo(now()->setTime(10, 30, 15, 123456));
    $this->actingAs($this->usuario)->withSession(['prueba_grupos' => true])
        ->withServerVariables(['REMOTE_ADDR' => '192.0.2.12'])->withHeader('User-Agent', 'Prueba de auditoría')
        ->patch(route('academico.alumnos.grupo.update', $this->alumno), [
            ...datosAsignacion($this->alumno, $this->grupo, 'Ingreso'),
            'usuario_id' => 999, 'login' => 'falso', 'session_id' => 'falsa', 'fecha_desde' => '1999-01-01',
        ])->assertSessionHasNoErrors();

    $asignacion = AlumnoGrupo::query()->sole();
    expect($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id)
        ->and($this->alumno->asignacionActual->grupo_id)->toBe($this->grupo->id)
        ->and($asignacion->fecha_desde->format('H:i:s.u'))->toBe('10:30:15.123456')
        ->and($asignacion->grupo_descripcion)->toBe($this->grupo->descripcion)
        ->and($asignacion->fecha_hasta)->toBeNull();

    $logs = Log::query()->orderBy('id')->get();
    expect($logs)->toHaveCount(2);
    foreach ($logs as $log) {
        expect($log->usuario_id)->toBe($this->usuario->id)
            ->and($log->login)->toBe($this->usuario->login)
            ->and($log->session_id)->not->toBeEmpty()->not->toBe('falsa')
            ->and($log->ip)->toBe('192.0.2.12')
            ->and($log->user_agent)->toBe('Prueba de auditoría')
            ->and($log->origen)->toBe('web')
            ->and($log->created_at->format('H:i:s'))->toBe('10:30:15');
    }
    expect($logs[0]->entidad)->toBe('alumnos_grupos')->and($logs[0]->entidad_id)->toBe($asignacion->id)
        ->and($logs[0]->accion)->toBe('create')->and($logs[0]->anterior)->toBeNull()
        ->and($logs[0]->nuevo['grupo_id'])->toBe($this->grupo->id)
        ->and($logs[1]->anterior['grupo_id'])->toBeNull()
        ->and($logs[1]->nuevo['grupo_id'])->toBe($this->grupo->id)
        ->and($logs->pluck('session_id')->unique())->toHaveCount(1);
});

it('cierra y abre cada traslado incluso al volver a un grupo anterior', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo, 'Ingreso');
    $otra = Grupo::factory()->create();
    $this->travel(1)->hours();
    asignarGrupoPrueba($this->alumno, $otra, 'Cambio de división');
    $this->travel(1)->hours();
    $otroUsuario = User::factory()->create();
    $this->actingAs($otroUsuario);
    asignarGrupoPrueba($this->alumno, $this->grupo, 'Regreso');

    $historial = $this->alumno->historialGrupos()->orderBy('id')->get();
    expect($historial)->toHaveCount(3)
        ->and($historial->pluck('grupo_id')->all())->toBe([$this->grupo->id, $otra->id, $this->grupo->id])
        ->and($historial[0]->fecha_hasta->equalTo($historial[1]->fecha_desde))->toBeTrue()
        ->and($historial[1]->fecha_hasta->equalTo($historial[2]->fecha_desde))->toBeTrue()
        ->and($historial[2]->fecha_hasta)->toBeNull()
        ->and($historial[0]->motivo_egreso)->toBe('Cambio de división')
        ->and($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id)
        ->and($this->alumno->asignacionActual()->count())->toBe(1);

    $cierre = $historial[1]->auditoria()->where('accion', 'update')->sole();
    expect($cierre->usuario_id)->toBe($otroUsuario->id)
        ->and($cierre->anterior['fecha_hasta'])->toBeNull()
        ->and($cierre->nuevo['fecha_hasta'])->not->toBeNull()
        ->and(Log::query()->count())->toBe(8);
});

it('permite cerrar y reasignar sin perder historia y no duplica una asignación idéntica', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    expect(AlumnoGrupo::query()->count())->toBe(1)->and(Log::query()->count())->toBe(2);
    asignarGrupoPrueba($this->alumno, null, 'Pase');
    expect($this->alumno->fresh()->grupo_id)->toBeNull()->and($this->alumno->asignacionActual()->exists())->toBeFalse();
    asignarGrupoPrueba($this->alumno, $this->grupo, 'Nuevo ingreso');
    expect(AlumnoGrupo::query()->count())->toBe(2)->and($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id);
});

it('rechaza fichas obsoletas incluso si el alumno vuelve a estar sin grupo', function () {
    $this->actingAs($this->usuario);
    $viejos = datosAsignacion($this->alumno, $this->grupo);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    asignarGrupoPrueba($this->alumno, null);
    $cantidadLogs = Log::query()->count();
    $this->patch(route('academico.alumnos.grupo.update', $this->alumno), $viejos)->assertSessionHasErrors('grupo_id');
    expect($this->alumno->fresh()->grupo_id)->toBeNull()
        ->and(AlumnoGrupo::query()->count())->toBe(1)->and(Log::query()->count())->toBe($cantidadLogs);
});

it('rechaza un traslado obsoleto aunque el grupo actual vuelva a ser el mismo', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $otra = Grupo::factory()->create();
    $viejos = datosAsignacion($this->alumno, $otra);
    asignarGrupoPrueba($this->alumno, $otra);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $this->patch(route('academico.alumnos.grupo.update', $this->alumno), $viejos)->assertSessionHasErrors('grupo_id');
    expect($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id)->and(AlumnoGrupo::query()->count())->toBe(3);
});

it('valida campos de la operación en servidor', function (string $campo, mixed $valor) {
    $this->actingAs($this->usuario)->patch(route('academico.alumnos.grupo.update', $this->alumno), [
        ...datosAsignacion($this->alumno, $this->grupo), $campo => $valor,
    ])->assertSessionHasErrors($campo);
    expect(AlumnoGrupo::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
})->with([
    ['grupo_id', 999999], ['grupo_id', 'texto'], ['motivo', ''], ['motivo', str_repeat('a', 501)],
    ['version_historial', null],
]);

it('rechaza asignaciones de alumnos o grupos inactivos', function (string $recurso) {
    $this->$recurso->update(['activo' => false]);
    $this->actingAs($this->usuario)->patch(route('academico.alumnos.grupo.update', $this->alumno), datosAsignacion($this->alumno, $this->grupo))
        ->assertSessionHasErrors('grupo_id');
    expect(AlumnoGrupo::query()->count())->toBe(0);
})->with(['alumno', 'grupo']);

it('impide cambiar el grupo desde la edición general del alumno o desde un guardado Eloquent aislado', function () {
    $this->actingAs($this->usuario)->patch(route('academico.alumnos.update', $this->alumno), [
        'legajo' => $this->alumno->legajo, 'apellido' => 'Pérez', 'nombre' => 'Ana', 'grupo_id' => $this->grupo->id,
    ])->assertSessionHasErrors('grupo_id');
    expect(fn () => $this->alumno->forceFill(['grupo_id' => $this->grupo->id])->save())->toThrow(LogicException::class);
    expect($this->alumno->fresh()->grupo_id)->toBeNull();
});

it('garantiza en la base una sola permanencia abierta por alumno', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $otra = Grupo::factory()->create();
    expect(fn () => DB::table('alumnos_grupos')->insert([
        'alumno_id' => $this->alumno->id, 'grupo_id' => $otra->id, 'grupo_descripcion' => 'Otra',
        'fecha_desde' => now(), 'motivo_ingreso' => 'Inserción simultánea',
    ]))->toThrow(QueryException::class);
    expect($this->alumno->asignacionActual()->count())->toBe(1);
});

it('cierra la permanencia en la baja y no la reabre al reactivar', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $this->travel(1)->hours();
    $motivo = MotivoBaja::query()->create(['nombre' => 'Pase', 'activo' => true]);
    $this->patch(route('academico.alumnos.desactivar', $this->alumno), [
        'fecha_baja' => now()->subDay()->toDateString(), 'motivo_baja_id' => $motivo->id,
    ])->assertSessionHasNoErrors();
    $asignacion = AlumnoGrupo::query()->sole();
    expect($this->alumno->fresh()->activo)->toBeFalse()->and($this->alumno->fresh()->grupo_id)->toBeNull()
        ->and($asignacion->fecha_hasta->greaterThanOrEqualTo($asignacion->fecha_desde))->toBeTrue()
        ->and($asignacion->motivo_egreso)->toBe('Baja del alumno')
        ->and($asignacion->auditoria()->count())->toBe(2);
    $this->patch(route('academico.alumnos.reactivar', $this->alumno))->assertSessionHasNoErrors();
    expect($this->alumno->fresh()->activo)->toBeTrue()->and($this->alumno->fresh()->grupo_id)->toBeNull()
        ->and($this->alumno->asignacionActual()->exists())->toBeFalse();
});

it('no desactiva un grupo ocupado y mantiene consultable el grupo cerrado inactivo', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $this->patch(route('academico.grupos.desactivar', $this->grupo))->assertSessionHasErrors('grupo');
    expect($this->grupo->fresh()->activo)->toBeTrue();
    asignarGrupoPrueba($this->alumno, null);
    $this->patch(route('academico.grupos.desactivar', $this->grupo))->assertSessionHasNoErrors();
    $this->get(route('academico.alumnos.show', ['alumno' => $this->alumno, 'seccion' => 'grupos']))
        ->assertInertia(fn (Assert $page) => $page->where('seccion', 'grupos')
            ->has('historialGrupos', 1)->where('historialGrupos.0.grupo_activo', false)->has('gruposDisponibles', 0));
});

it('protege la composición y el plan de un grupo con historia incluso cerrada', function (string $campo) {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    asignarGrupoPrueba($this->alumno, null);
    $datos = $this->grupo->only(['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id', 'plan_estudio_id']);
    $datos[$campo] = $campo === 'ciclo_lectivo' ? $this->grupo->ciclo_lectivo + 1
        : PlanEstudio::query()->create(['nivel_id' => $this->grupo->nivel_id, 'descripcion' => 'Nuevo plan'])->id;
    $this->patch(route('academico.grupos.update', $this->grupo), $datos)->assertSessionHasErrors($campo);
    expect($this->grupo->fresh()->getAttribute($campo))->toBe($this->grupo->getAttribute($campo));
})->with(['ciclo_lectivo', 'plan_estudio_id']);

it('conserva el nombre histórico y muestra alumnos actuales desde el grupo', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $descripcion = AlumnoGrupo::query()->sole()->grupo_descripcion;
    $this->grupo->curso->update(['descripcion' => 'Nombre corregido']);
    $this->get(route('academico.alumnos.show', ['alumno' => $this->alumno, 'seccion' => 'grupos']))
        ->assertInertia(fn (Assert $page) => $page->where('alumno.grupo_id', $this->grupo->id)
            ->where('historialGrupos.0.grupo_descripcion', $descripcion)
            ->where('historialGrupos.0.auditoria.0.login', $this->usuario->login)
            ->missing('historialGrupos.0.auditoria.0.session_id'));
    $this->get(route('academico.grupos.show', $this->grupo))->assertInertia(fn (Assert $page) => $page
        ->has('alumnos', 1)->where('alumnos.0.id', $this->alumno->id));
});

it('revierte historial grupo actual y logs cuando falla la auditoría del traslado', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    $otra = Grupo::factory()->create();
    $this->withoutExceptionHandling();
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'alumnos') {
            throw new RuntimeException('Auditoría no disponible');
        }
    });
    try {
        expect(fn () => $this->patch(route('academico.alumnos.grupo.update', $this->alumno), datosAsignacion($this->alumno, $otra)))
            ->toThrow(RuntimeException::class, 'Auditoría no disponible');
    } finally {
        Log::flushEventListeners();
    }
    expect($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id)
        ->and(AlumnoGrupo::query()->count())->toBe(1)
        ->and(AlumnoGrupo::query()->sole()->fecha_hasta)->toBeNull()
        ->and(Log::query()->count())->toBe(2);
});

it('detecta inconsistencias previas sin repararlas silenciosamente', function () {
    DB::table('alumnos')->where('id', $this->alumno->id)->update(['grupo_id' => $this->grupo->id]);
    $this->actingAs($this->usuario)->patch(route('academico.alumnos.grupo.update', $this->alumno), datosAsignacion($this->alumno, null))
        ->assertSessionHasErrors('grupo_id');
    expect($this->alumno->fresh()->grupo_id)->toBe($this->grupo->id)->and(AlumnoGrupo::query()->count())->toBe(0);
});

it('no permite borrar ni reabrir una permanencia cerrada', function () {
    $this->actingAs($this->usuario);
    asignarGrupoPrueba($this->alumno, $this->grupo);
    asignarGrupoPrueba($this->alumno, null);
    $asignacion = AlumnoGrupo::query()->sole();
    expect(fn () => $asignacion->delete())->toThrow(LogicException::class);
    expect(fn () => $asignacion->forceFill(['fecha_hasta' => null])->save())->toThrow(LogicException::class);
    expect(AlumnoGrupo::query()->sole()->fecha_hasta)->not->toBeNull();
});
