<?php

use App\Models\Curso;
use App\Models\Nivel;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('carga el índice de cursos', function () {
    Curso::factory()->count(3)->create();

    $this->get(route('academico.cursos.index'))->assertOk();
});

it('valida, crea y audita un curso', function () {
    $nivel = Nivel::factory()->create();

    $this->post(route('academico.cursos.store'), [
        'activo' => true,
        'nivel_id' => $nivel->id,
        'codigo' => 'PRI-1',
        'descripcion' => '1er Grado',
        'orden' => 1,
    ])->assertRedirect(route('academico.cursos.index'));

    $this->assertDatabaseHas('cursos', ['nivel_id' => $nivel->id, 'codigo' => 'PRI-1', 'orden' => 1]);
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'accion' => 'create']);
});

it('rechaza un curso sin nivel', function () {
    $this->post(route('academico.cursos.store'), [
        'activo' => true,
        'codigo' => 'X-1',
        'descripcion' => 'Sin nivel',
        'orden' => 1,
    ])->assertSessionHasErrors('nivel_id');
});

it('rechaza un nivel inexistente', function () {
    $this->post(route('academico.cursos.store'), [
        'activo' => true,
        'nivel_id' => 9999,
        'codigo' => 'X-1',
        'descripcion' => 'Nivel inexistente',
        'orden' => 1,
    ])->assertSessionHasErrors('nivel_id');
});

it('actualiza y audita un curso', function () {
    $curso = Curso::factory()->create(['descripcion' => '1er Grado']);

    $this->put(route('academico.cursos.update', $curso), [
        'activo' => true,
        'nivel_id' => $curso->nivel_id,
        'codigo' => $curso->codigo,
        'descripcion' => '1er Grado Actualizado',
        'orden' => $curso->orden,
    ])->assertRedirect(route('academico.cursos.index'));

    expect($curso->refresh()->descripcion)->toBe('1er Grado Actualizado');
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'entidad_id' => $curso->id, 'accion' => 'update']);
});

it('da de baja lógica y audita un curso', function () {
    $curso = Curso::factory()->create(['activo' => true]);

    $this->patch(route('academico.cursos.desactivar', $curso))
        ->assertRedirect(route('academico.cursos.index'));

    expect($curso->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'entidad_id' => $curso->id, 'accion' => 'deactivate']);
});
