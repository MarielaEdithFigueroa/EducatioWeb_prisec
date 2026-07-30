<?php

use App\Models\Division;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('carga el índice de divisiones', function () {
    Division::factory()->count(3)->create();

    $this->get(route('academico.divisiones.index'))->assertOk();
});

it('valida, crea y audita una división', function () {
    $this->post(route('academico.divisiones.store'), [
        'activo' => true,
        'codigo' => 'A',
        'descripcion' => 'División A',
    ])->assertRedirect(route('academico.divisiones.index'));

    $this->assertDatabaseHas('divisiones', ['codigo' => 'A', 'activo' => true]);
    $this->assertDatabaseHas('logs', ['entidad' => 'divisiones', 'accion' => 'create']);
});

it('rechaza una división sin código', function () {
    $this->post(route('academico.divisiones.store'), [
        'activo' => true,
        'descripcion' => 'Sin código',
    ])->assertSessionHasErrors('codigo');
});

it('rechaza un código duplicado', function () {
    Division::factory()->create(['codigo' => 'A']);

    $this->post(route('academico.divisiones.store'), [
        'activo' => true,
        'codigo' => 'A',
        'descripcion' => 'Duplicada',
    ])->assertSessionHasErrors('codigo');
});

it('actualiza y audita una división', function () {
    $division = Division::factory()->create(['codigo' => 'A', 'descripcion' => 'División A']);

    $this->put(route('academico.divisiones.update', $division), [
        'activo' => true,
        'codigo' => 'A',
        'descripcion' => 'División A Actualizada',
    ])->assertRedirect(route('academico.divisiones.index'));

    expect($division->refresh()->descripcion)->toBe('División A Actualizada');
    $this->assertDatabaseHas('logs', ['entidad' => 'divisiones', 'entidad_id' => $division->id, 'accion' => 'update']);
});

it('da de baja lógica y audita una división', function () {
    $division = Division::factory()->create(['activo' => true]);

    $this->patch(route('academico.divisiones.desactivar', $division))
        ->assertRedirect(route('academico.divisiones.index'));

    expect($division->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'divisiones', 'entidad_id' => $division->id, 'accion' => 'deactivate']);
});
