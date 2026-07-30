<?php

use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Turno;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function payloadGrupo(array $overrides = []): array
{
    return array_merge([
        'activo' => true,
        'anio_lectivo_id' => AnioLectivo::factory()->create()->id,
        'curso_id' => Curso::factory()->create()->id,
        'division_id' => Division::factory()->create()->id,
        'turno_id' => Turno::factory()->create()->id,
    ], $overrides);
}

it('carga el índice de grupos', function () {
    Grupo::factory()->count(3)->create();

    $this->get(route('academico.grupos.index'))->assertOk();
});

it('valida, crea y audita un grupo', function () {
    $payload = payloadGrupo();

    $this->post(route('academico.grupos.store'), $payload)
        ->assertRedirect(route('academico.grupos.index'));

    $this->assertDatabaseHas('grupos', [
        'anio_lectivo_id' => $payload['anio_lectivo_id'],
        'curso_id' => $payload['curso_id'],
        'division_id' => $payload['division_id'],
        'turno_id' => $payload['turno_id'],
    ]);
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'accion' => 'create']);
});

it('rechaza un grupo sin curso', function () {
    $this->post(route('academico.grupos.store'), payloadGrupo(['curso_id' => null]))
        ->assertSessionHasErrors('curso_id');
});

it('rechaza una combinación duplicada', function () {
    $grupo = Grupo::factory()->create();

    $this->post(route('academico.grupos.store'), [
        'activo' => true,
        'anio_lectivo_id' => $grupo->anio_lectivo_id,
        'curso_id' => $grupo->curso_id,
        'division_id' => $grupo->division_id,
        'turno_id' => $grupo->turno_id,
    ])->assertSessionHasErrors('turno_id');

    expect(Grupo::count())->toBe(1);
});

it('actualiza y audita un grupo', function () {
    $grupo = Grupo::factory()->create();
    $otraDivision = Division::factory()->create();

    $this->put(route('academico.grupos.update', $grupo), [
        'activo' => true,
        'anio_lectivo_id' => $grupo->anio_lectivo_id,
        'curso_id' => $grupo->curso_id,
        'division_id' => $otraDivision->id,
        'turno_id' => $grupo->turno_id,
    ])->assertRedirect(route('academico.grupos.index'));

    expect($grupo->refresh()->division_id)->toBe($otraDivision->id);
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'entidad_id' => $grupo->id, 'accion' => 'update']);
});

it('permite editar un grupo conservando su propia combinación', function () {
    $grupo = Grupo::factory()->create();

    $this->put(route('academico.grupos.update', $grupo), [
        'activo' => false,
        'anio_lectivo_id' => $grupo->anio_lectivo_id,
        'curso_id' => $grupo->curso_id,
        'division_id' => $grupo->division_id,
        'turno_id' => $grupo->turno_id,
    ])->assertRedirect(route('academico.grupos.index'));

    expect($grupo->refresh()->activo)->toBeFalse();
});

it('da de baja lógica y audita un grupo', function () {
    $grupo = Grupo::factory()->create(['activo' => true]);

    $this->patch(route('academico.grupos.desactivar', $grupo))
        ->assertRedirect(route('academico.grupos.index'));

    expect($grupo->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'entidad_id' => $grupo->id, 'accion' => 'deactivate']);
});
