<?php

use App\Models\Nivel;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('carga el índice de niveles', function () {
    Nivel::factory()->count(3)->create();

    $this->get(route('academico.niveles.index'))->assertOk();
});

it('valida, crea y audita un nivel', function () {
    $this->post(route('academico.niveles.store'), [
        'activo' => true,
        'codigo' => 'PRI',
        'descripcion' => 'Primaria',
    ])->assertRedirect(route('academico.niveles.index'));

    $this->assertDatabaseHas('niveles', ['codigo' => 'PRI', 'descripcion' => 'Primaria', 'activo' => true]);
    $this->assertDatabaseHas('logs', ['entidad' => 'niveles', 'accion' => 'create']);
});

it('rechaza un nivel sin código', function () {
    $this->post(route('academico.niveles.store'), [
        'activo' => true,
        'descripcion' => 'Sin código',
    ])->assertSessionHasErrors('codigo');

    $this->assertDatabaseMissing('niveles', ['descripcion' => 'Sin código']);
});

it('rechaza un código duplicado', function () {
    Nivel::factory()->create(['codigo' => 'PRI']);

    $this->post(route('academico.niveles.store'), [
        'activo' => true,
        'codigo' => 'PRI',
        'descripcion' => 'Duplicado',
    ])->assertSessionHasErrors('codigo');
});

it('actualiza y audita un nivel', function () {
    $nivel = Nivel::factory()->create(['codigo' => 'PRI', 'descripcion' => 'Primaria']);

    $this->put(route('academico.niveles.update', $nivel), [
        'activo' => true,
        'codigo' => 'PRI',
        'descripcion' => 'Primaria Actualizada',
    ])->assertRedirect(route('academico.niveles.index'));

    expect($nivel->refresh()->descripcion)->toBe('Primaria Actualizada');
    $this->assertDatabaseHas('logs', ['entidad' => 'niveles', 'entidad_id' => $nivel->id, 'accion' => 'update']);
});

it('da de baja lógica y audita un nivel', function () {
    $nivel = Nivel::factory()->create(['activo' => true]);

    $this->patch(route('academico.niveles.desactivar', $nivel))
        ->assertRedirect(route('academico.niveles.index'));

    expect($nivel->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'niveles', 'entidad_id' => $nivel->id, 'accion' => 'deactivate']);
});

it('reactiva y audita un nivel', function () {
    $nivel = Nivel::factory()->inactivo()->create();

    $this->patch(route('academico.niveles.reactivar', $nivel))
        ->assertRedirect(route('academico.niveles.index'));

    expect($nivel->refresh()->activo)->toBeTrue();
    $this->assertDatabaseHas('logs', ['entidad' => 'niveles', 'entidad_id' => $nivel->id, 'accion' => 'reactivate']);
});
