<?php

use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Log;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use App\Models\User;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

function datosGrupo(Grupo $grupo): array
{
    return $grupo->only(['ciclo_lectivo', 'nivel_id', 'curso_id', 'division_id', 'turno_id', 'plan_estudio_id']);
}

it('protege todas las acciones de grupos con autenticación', function () {
    $grupo = Grupo::factory()->create();

    foreach (['index', 'create', 'show', 'edit'] as $accion) {
        $this->get(route('academico.grupos.'.$accion, in_array($accion, ['show', 'edit']) ? $grupo : []))
            ->assertRedirect(route('login'));
    }

    $this->post(route('academico.grupos.store'), datosGrupo($grupo))->assertRedirect(route('login'));

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.grupos.'.$accion, $grupo), datosGrupo($grupo))->assertRedirect(route('login'));
    }
});

it('presenta listado alta consulta y edición con contexto de filtros', function () {
    $grupo = Grupo::factory()->create(['ciclo_lectivo' => 2026]);
    $filtros = ['ciclo_lectivo' => '2026', 'estado' => 'activos'];
    $this->actingAs(User::factory()->create());

    $this->get(route('academico.grupos.index', $filtros))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('academico/grupos/index')
            ->has('grupos', 1)->where('grupos.0.id', $grupo->id)
            ->where('filters.ciclo_lectivo', '2026')->where('resultados.total', 1)
            ->has('catalogos.niveles')->has('catalogos.cursos'));

    $this->get(route('academico.grupos.create', ['filtros' => $filtros]))->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('academico/grupos/formulario')
            ->where('grupo', null)->where('filters.estado', 'activos')
            ->where('iniciales.ciclo_lectivo', now()->year));

    foreach (['show' => 'show', 'edit' => 'formulario'] as $accion => $pagina) {
        $this->get(route('academico.grupos.'.$accion, ['grupo' => $grupo, 'filtros' => $filtros]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('academico/grupos/'.$pagina)
                ->where('grupo.id', $grupo->id)->where('grupo.descripcion', $grupo->descripcion)
                ->where('filters.ciclo_lectivo', '2026'));
    }
});

it('crea un grupo con descripción automática y auditoría ignorando atributos ajenos', function () {
    $usuario = User::factory()->create();
    $datos = datosGrupo(Grupo::factory()->make(['ciclo_lectivo' => 2026]));
    $this->actingAs($usuario)->post(route('academico.grupos.store'), [
        ...$datos, 'activo' => false, 'dh_descripcion' => 'Nombre arbitrario', 'alumno_id' => 99,
        'filtros' => ['ciclo_lectivo' => '2026'],
    ])->assertSessionHasNoErrors()->assertRedirect(route('academico.grupos.index', ['ciclo_lectivo' => '2026']));

    $grupo = Grupo::query()->sole();
    expect($grupo->activo)->toBeTrue()
        ->and($grupo->plan_estudio_id)->toBeNull()
        ->and($grupo->dh_descripcion)->toBe("2026 · Primaria · 1.º grado {$grupo->division->descripcion} · Mañana");

    $log = Log::query()->where('entidad', 'grupos')->sole();
    expect($log->accion)->toBe('create')->and($log->anterior)->toBeNull()
        ->and($log->nuevo['dh_descripcion'])->toBe($grupo->dh_descripcion)
        ->and($log->usuario_id)->toBe($usuario->id);
});

it('guarda y prepara otro conservando sólo ciclo nivel turno y plan', function () {
    $grupo = Grupo::factory()->make(['ciclo_lectivo' => 2027]);
    $plan = PlanEstudio::query()->create(['nivel_id' => $grupo->nivel_id, 'descripcion' => 'Plan general']);
    $datos = [...datosGrupo($grupo), 'plan_estudio_id' => $plan->id];

    $this->actingAs(User::factory()->create())->post(route('academico.grupos.store'), [
        ...$datos, 'crear_otro' => true,
    ])->assertSessionHasNoErrors()->assertRedirect(route('academico.grupos.create'))
        ->assertSessionHas('nuevo_grupo', [
            'ciclo_lectivo' => 2027, 'nivel_id' => $grupo->nivel_id,
            'turno_id' => $grupo->turno_id, 'plan_estudio_id' => $plan->id,
        ]);

    $this->get(route('academico.grupos.create'))->assertInertia(fn (Assert $page) => $page
        ->where('iniciales.ciclo_lectivo', 2027)->where('iniciales.plan_estudio_id', $plan->id)
        ->missing('iniciales.curso_id')->missing('iniciales.division_id'));
    expect(Grupo::query()->count())->toBe(1);
});

it('rechaza duplicados activos o inactivos incluso con otro plan', function (bool $activo) {
    $grupo = Grupo::factory()->create(['activo' => $activo]);
    $plan = PlanEstudio::query()->create(['nivel_id' => $grupo->nivel_id, 'descripcion' => 'Otro plan']);

    $this->actingAs(User::factory()->create())->post(route('academico.grupos.store'), [
        ...datosGrupo($grupo), 'plan_estudio_id' => $plan->id,
    ])->assertSessionHasErrors('ciclo_lectivo');

    expect(Grupo::query()->count())->toBe(1)->and(Log::query()->where('entidad', 'grupos')->count())->toBe(0);
})->with([true, false]);

it('valida composición y referencias en servidor', function (string $campo, mixed $valor) {
    $datos = datosGrupo(Grupo::factory()->make());
    $this->actingAs(User::factory()->create())->post(route('academico.grupos.store'), [...$datos, $campo => $valor])
        ->assertSessionHasErrors($campo);
    expect(Grupo::query()->count())->toBe(0);
})->with([
    ['ciclo_lectivo', null], ['ciclo_lectivo', 1899], ['ciclo_lectivo', 10000],
    ['ciclo_lectivo', '2026.5'], ['nivel_id', null], ['curso_id', null],
    ['division_id', null], ['turno_id', null], ['plan_estudio_id', 999999],
    ['crear_otro', 'no es booleano'],
]);

it('exige que curso y plan pertenezcan al nivel del grupo', function () {
    $datos = datosGrupo(Grupo::factory()->make());
    $otroNivel = Nivel::query()->create(['codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria']);
    $curso = Curso::query()->create(['nivel_id' => $otroNivel->id, 'descripcion' => '2.º año']);
    $plan = PlanEstudio::query()->create(['nivel_id' => $otroNivel->id, 'descripcion' => 'Bachillerato']);

    $this->actingAs(User::factory()->create())->post(route('academico.grupos.store'), [
        ...$datos, 'curso_id' => $curso->id, 'plan_estudio_id' => $plan->id,
    ])->assertSessionHasErrors(['curso_id', 'plan_estudio_id']);

    expect(Grupo::query()->count())->toBe(0);
});

it('impide altas con referencias inactivas y permite conservarlas al editar', function (string $campo, string $relacion) {
    $grupo = Grupo::factory()->create();
    $plan = PlanEstudio::query()->create(['nivel_id' => $grupo->nivel_id, 'descripcion' => 'Plan general']);
    $grupo->update(['plan_estudio_id' => $plan->id]);
    $grupo->$relacion->update(['activo' => false]);
    $datos = [...datosGrupo($grupo), 'ciclo_lectivo' => $grupo->ciclo_lectivo + 1];

    $this->actingAs(User::factory()->create())->post(route('academico.grupos.store'), $datos)
        ->assertSessionHasErrors($campo);
    $this->patch(route('academico.grupos.update', $grupo), $datos)
        ->assertSessionHasNoErrors()->assertRedirect(route('academico.grupos.show', $grupo));

    expect($grupo->refresh()->ciclo_lectivo)->toBe($datos['ciclo_lectivo']);
})->with([
    ['nivel_id', 'nivel'], ['curso_id', 'curso'], ['division_id', 'division'],
    ['turno_id', 'turno'], ['plan_estudio_id', 'planEstudio'],
]);

it('no permite reemplazar una referencia por otra inactiva ni conservar curso de otro nivel', function () {
    $grupo = Grupo::factory()->create();
    $division = Division::query()->create(['descripcion' => 'Inactiva', 'activo' => false]);
    $otroNivel = Nivel::query()->create(['codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria']);
    $this->actingAs(User::factory()->create())
        ->patch(route('academico.grupos.update', $grupo), [...datosGrupo($grupo), 'division_id' => $division->id])
        ->assertSessionHasErrors('division_id');
    $this->patch(route('academico.grupos.update', $grupo), [...datosGrupo($grupo), 'nivel_id' => $otroNivel->id])
        ->assertSessionHasErrors('curso_id');
});

it('actualiza la composición y descripción sin reactivar y audita sólo cambios reales', function () {
    $grupo = Grupo::factory()->create(['activo' => false, 'ciclo_lectivo' => 2026]);
    $nuevoCurso = Curso::query()->create(['nivel_id' => $grupo->nivel_id, 'descripcion' => '2.º grado']);
    $anterior = $grupo->dh_descripcion;
    $datos = [...datosGrupo($grupo), 'curso_id' => $nuevoCurso->id, 'activo' => true];
    $this->actingAs(User::factory()->create());

    for ($i = 0; $i < 2; $i++) {
        $this->patch(route('academico.grupos.update', $grupo), $datos)->assertSessionHasNoErrors();
    }

    $grupo->refresh();
    expect($grupo->activo)->toBeFalse()
        ->and($grupo->dh_descripcion)->toBe("2026 · Primaria · 2.º grado {$grupo->division->descripcion} · Mañana");

    $log = Log::query()->where('entidad', 'grupos')->sole();
    expect($log->accion)->toBe('update')
        ->and($log->anterior['dh_descripcion'])->toBe($anterior)
        ->and($log->nuevo['dh_descripcion'])->toBe($grupo->dh_descripcion);
});

it('impide editar un grupo para duplicar otro', function () {
    $grupo = Grupo::factory()->create();
    $otro = Grupo::factory()->create();
    $this->actingAs(User::factory()->create())->patch(route('academico.grupos.update', $otro), datosGrupo($grupo))
        ->assertSessionHasErrors('ciclo_lectivo');
    expect($otro->refresh()->division_id)->not->toBe($grupo->division_id);
});

it('da de baja y reactiva sin eliminar ni duplicar logs', function () {
    $grupo = Grupo::factory()->create();
    $this->actingAs(User::factory()->create());

    foreach (['desactivar' => false, 'reactivar' => true] as $accion => $activo) {
        for ($i = 0; $i < 2; $i++) {
            $this->from(route('academico.grupos.index'))->patch(route('academico.grupos.'.$accion, $grupo))
                ->assertRedirect(route('academico.grupos.index'));
        }

        expect($grupo->refresh()->activo)->toBe($activo);
    }

    expect(Grupo::query()->count())->toBe(1)
        ->and(Log::query()->where('entidad', 'grupos')->orderBy('id')->pluck('accion')->all())
        ->toBe(['deactivate', 'reactivate']);
});

it('filtra por cada campo y conserva las opciones históricas inactivas', function () {
    $grupo = Grupo::factory()->create(['ciclo_lectivo' => 2026, 'activo' => false]);
    $plan = PlanEstudio::query()->create(['nivel_id' => $grupo->nivel_id, 'descripcion' => 'Plan histórico', 'activo' => false]);
    $grupo->update(['plan_estudio_id' => $plan->id]);
    $grupo->division->update(['activo' => false]);
    $otroNivel = Nivel::query()->create(['codigo' => 'SECUNDARIA', 'descripcion' => 'Secundaria']);
    $otroTurno = Turno::query()->create(['descripcion' => 'Tarde']);
    Grupo::factory()->create(['nivel_id' => $otroNivel->id, 'ciclo_lectivo' => 2027, 'turno_id' => $otroTurno->id]);
    $this->actingAs(User::factory()->create());

    foreach ([...datosGrupo($grupo), 'estado' => 'inactivos'] as $campo => $valor) {
        $this->get(route('academico.grupos.index', [$campo => $valor]))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('grupos', 1)
                ->where('grupos.0.id', $grupo->id)->where('filters.'.$campo, (string) $valor)
                ->where('resultados.total', 2)->where('resultados.filtrados', 1)
                ->has('catalogos.planes_estudio', 1)->where('catalogos.planes_estudio.0.activo', false));
    }

    $this->get(route('academico.grupos.index', ['estado' => 'activos']))->assertInertia(fn (Assert $page) => $page
        ->has('grupos', 1)->where('grupos.0.activo', true));
    $this->get(route('academico.grupos.index', ['nivel_id' => $otroNivel->id, 'curso_id' => $grupo->curso_id]))
        ->assertInertia(fn (Assert $page) => $page->has('grupos', 0)
            ->where('filters.nivel_id', (string) $otroNivel->id)->where('filters.curso_id', (string) $grupo->curso_id));
});

it('valida filtros y contexto de retorno sin aceptar URLs arbitrarias', function () {
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.index', ['estado' => 'cualquiera']))
        ->assertSessionHasErrors('estado');
    $this->get(route('academico.grupos.index', ['nivel_id' => 99999]))->assertSessionHasErrors('nivel_id');
    $this->get(route('academico.grupos.create', ['filtros' => ['url' => 'https://example.com']]))
        ->assertSessionHasErrors('filtros');
});

it('muestra los nombres vigentes de catálogos sin depender de la descripción guardada', function () {
    $grupo = Grupo::factory()->create();
    $grupo->curso->update(['descripcion' => 'Primer grado']);
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.show', $grupo))
        ->assertInertia(fn (Assert $page) => $page->where('grupo.descripcion', fn (string $valor): bool => str_contains($valor, 'Primer grado')));
});

it('ofrece todos los ciclos desde el primero registrado hasta el próximo año sin depender de los filtros', function () {
    $this->travelTo(now()->setDate(2026, 10, 19));
    Grupo::factory()->create(['ciclo_lectivo' => 2022, 'activo' => false]);
    Grupo::factory()->create(['ciclo_lectivo' => 2026]);

    $this->actingAs(User::factory()->create())
        ->get(route('academico.grupos.index', ['ciclo_lectivo' => 2026, 'estado' => 'activos']))
        ->assertInertia(fn (Assert $page) => $page
            ->where('ciclosLectivos', [2022, 2023, 2024, 2025, 2026, 2027])
            ->has('grupos', 1));

    $this->get(route('academico.grupos.index', ['ciclo_lectivo' => 1903]))
        ->assertInertia(fn (Assert $page) => $page
            ->where('filters.ciclo_lectivo', '1903')
            ->where('ciclosLectivos', [2022, 2023, 2024, 2025, 2026, 2027])
            ->has('grupos', 0));
});

it('ofrece el año actual y el próximo cuando todavía no hay grupos', function () {
    $this->travelTo(now()->setDate(2026, 10, 19));

    $this->actingAs(User::factory()->create())->get(route('academico.grupos.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('ciclosLectivos', [2026, 2027])
            ->has('grupos', 0));
});

it('revierte el alta si falla la auditoría', function () {
    $datos = datosGrupo(Grupo::factory()->make());
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('Auditoría no disponible.');
    });

    expect(fn () => $this->post(route('academico.grupos.store'), $datos))
        ->toThrow(RuntimeException::class, 'Auditoría no disponible.');
    expect(Grupo::query()->count())->toBe(0);
});
