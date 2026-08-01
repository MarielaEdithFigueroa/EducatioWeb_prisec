<?php

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use App\Models\User;

/** @return array{nivel: Nivel, anio: AnioLectivo, plan: PlanEstudio, curso: Curso, division: Division, turno: Turno, datos: array<string, int>} */
function estructuraValidaParaGrupo(): array
{
    $nivel = Nivel::factory()->create();
    $anio = AnioLectivo::factory()->create();
    $plan = PlanEstudio::factory()->for($nivel)->create();
    $curso = Curso::factory()->for($nivel)->create();
    $division = Division::factory()->for($nivel)->create();
    $turno = Turno::factory()->for($nivel)->create();

    return [
        'nivel' => $nivel,
        'anio' => $anio,
        'plan' => $plan,
        'curso' => $curso,
        'division' => $division,
        'turno' => $turno,
        'datos' => [
            'anio_lectivo_id' => $anio->id,
            'plan_estudio_id' => $plan->id,
            'curso_id' => $curso->id,
            'division_id' => $division->id,
            'turno_id' => $turno->id,
        ],
    ];
}

it('requiere autenticación para administrar grupos', function () {
    $this->get(route('academico.grupos.index'))->assertRedirect(route('login'));
    $this->post(route('academico.grupos.store'))->assertRedirect(route('login'));
});

it('crea un grupo válido y lo audita', function () {
    $estructura = estructuraValidaParaGrupo();

    $this->actingAs(User::factory()->create())
        ->post(route('academico.grupos.store'), $estructura['datos'])
        ->assertRedirect(route('academico.grupos.index'));

    $grupo = Grupo::query()->firstOrFail();
    expect($grupo->activo)->toBeTrue();
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'entidad_id' => $grupo->id, 'accion' => 'create']);
});

it('rechaza componentes de niveles distintos y referencias inactivas', function () {
    $estructura = estructuraValidaParaGrupo();
    $otroNivel = Nivel::factory()->create();
    $turnoOtroNivel = Turno::factory()->for($otroNivel)->create();

    $this->actingAs(User::factory()->create())
        ->post(route('academico.grupos.store'), [...$estructura['datos'], 'turno_id' => $turnoOtroNivel->id])
        ->assertSessionHasErrors('nivel_id');

    $estructura['plan']->update(['activo' => false]);
    $this->post(route('academico.grupos.store'), $estructura['datos'])
        ->assertSessionHasErrors('plan_estudio_id');

    $this->assertDatabaseCount('grupos', 0);
});

it('rechaza una combinación activa duplicada y reactiva la inactiva', function () {
    $estructura = estructuraValidaParaGrupo();
    $grupo = Grupo::factory()->create([...$estructura['datos'], 'activo' => true]);
    $usuario = User::factory()->create();

    $this->actingAs($usuario)
        ->post(route('academico.grupos.store'), $estructura['datos'])
        ->assertSessionHasErrors('turno_id');

    $grupo->update(['activo' => false]);
    $this->post(route('academico.grupos.store'), $estructura['datos'])->assertRedirect();

    expect($grupo->refresh()->activo)->toBeTrue();
    $this->assertDatabaseCount('grupos', 1);
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'entidad_id' => $grupo->id, 'accion' => 'reactivate']);
});

it('permite conservar una referencia histórica inactiva al editar', function () {
    $estructura = estructuraValidaParaGrupo();
    $grupo = Grupo::factory()->create($estructura['datos']);
    $estructura['turno']->update(['activo' => false]);

    $this->actingAs(User::factory()->create())
        ->patch(route('academico.grupos.update', $grupo), $estructura['datos'])
        ->assertSessionHasNoErrors();
});

it('aplica baja lógica y sólo reactiva grupos todavía válidos', function () {
    $estructura = estructuraValidaParaGrupo();
    $grupo = Grupo::factory()->create($estructura['datos']);
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->patch(route('academico.grupos.desactivar', $grupo))->assertRedirect();
    expect($grupo->refresh()->activo)->toBeFalse();

    $estructura['anio']->update(['estado' => EstadoAnioLectivo::Cerrado]);
    $this->patch(route('academico.grupos.reactivar', $grupo))->assertSessionHasErrors('activo');
    expect($grupo->refresh()->activo)->toBeFalse();

    $estructura['anio']->update(['estado' => EstadoAnioLectivo::Preparacion]);
    $this->patch(route('academico.grupos.reactivar', $grupo))->assertRedirect();
    expect($grupo->refresh()->activo)->toBeTrue();
});

it('protege el nivel y los catálogos utilizados por grupos activos', function () {
    $estructura = estructuraValidaParaGrupo();
    $grupo = Grupo::factory()->create($estructura['datos']);
    $otroNivel = Nivel::factory()->create();
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->patch(route('academico.cursos.update', $estructura['curso']), [
        'nivel_id' => $otroNivel->id,
        'descripcion' => $estructura['curso']->descripcion,
        'orden' => $estructura['curso']->orden,
    ])->assertSessionHasErrors('nivel_id');

    $this->patch(route('academico.cursos.desactivar', $estructura['curso']))
        ->assertSessionHasErrors('activo');

    $this->patch(route('academico.grupos.desactivar', $grupo))->assertRedirect();
    $this->patch(route('academico.cursos.desactivar', $estructura['curso']))->assertRedirect();

    expect($estructura['curso']->refresh()->activo)->toBeFalse();
});

it('genera por factory un grupo consistente por defecto', function () {
    $grupo = Grupo::factory()->create()->load(['planEstudio', 'curso', 'division', 'turno']);

    expect(collect([
        $grupo->planEstudio->nivel_id,
        $grupo->curso->nivel_id,
        $grupo->division->nivel_id,
        $grupo->turno->nivel_id,
    ])->unique())->toHaveCount(1);
});
