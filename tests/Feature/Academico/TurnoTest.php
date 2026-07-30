<?php

use App\Models\Turno;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('carga el índice de turnos', function () {
    Turno::factory()->count(3)->create();

    $this->get(route('academico.turnos.index'))->assertOk();
});

it('valida, crea y audita un turno', function () {
    $this->post(route('academico.turnos.store'), [
        'activo' => true,
        'codigo' => 'MAN',
        'descripcion' => 'Mañana',
    ])->assertRedirect(route('academico.turnos.index'));

    $this->assertDatabaseHas('turnos', ['codigo' => 'MAN', 'descripcion' => 'Mañana', 'activo' => true]);
    $this->assertDatabaseHas('logs', ['entidad' => 'turnos', 'accion' => 'create']);
});

it('rechaza un turno sin código', function () {
    $this->post(route('academico.turnos.store'), [
        'activo' => true,
        'descripcion' => 'Sin código',
    ])->assertSessionHasErrors('codigo');
});

it('rechaza un código duplicado', function () {
    Turno::factory()->create(['codigo' => 'MAN']);

    $this->post(route('academico.turnos.store'), [
        'activo' => true,
        'codigo' => 'MAN',
        'descripcion' => 'Duplicado',
    ])->assertSessionHasErrors('codigo');
});

it('actualiza y audita un turno', function () {
    $turno = Turno::factory()->create(['codigo' => 'MAN', 'descripcion' => 'Mañana']);

    $this->put(route('academico.turnos.update', $turno), [
        'activo' => true,
        'codigo' => 'MAN',
        'descripcion' => 'Mañana Actualizado',
    ])->assertRedirect(route('academico.turnos.index'));

    expect($turno->refresh()->descripcion)->toBe('Mañana Actualizado');
    $this->assertDatabaseHas('logs', ['entidad' => 'turnos', 'entidad_id' => $turno->id, 'accion' => 'update']);
});

it('da de baja lógica y audita un turno', function () {
    $turno = Turno::factory()->create(['activo' => true]);

    $this->patch(route('academico.turnos.desactivar', $turno))
        ->assertRedirect(route('academico.turnos.index'));

    expect($turno->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'turnos', 'entidad_id' => $turno->id, 'accion' => 'deactivate']);
});
