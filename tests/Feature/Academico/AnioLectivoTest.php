<?php

use App\Models\AnioLectivo;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('carga el índice de años lectivos', function () {
    AnioLectivo::factory()->count(3)->create();

    $this->get(route('academico.anios-lectivos.index'))->assertOk();
});

it('valida, crea y audita un año lectivo', function () {
    $this->post(route('academico.anios-lectivos.store'), [
        'activo' => true,
        'anio' => 2026,
        'vigente' => true,
    ])->assertRedirect(route('academico.anios-lectivos.index'));

    $this->assertDatabaseHas('anios_lectivos', ['anio' => 2026, 'vigente' => true]);
    $this->assertDatabaseHas('logs', ['entidad' => 'anios_lectivos', 'accion' => 'create']);
});

it('rechaza un año fuera de rango', function () {
    $this->post(route('academico.anios-lectivos.store'), [
        'activo' => true,
        'anio' => 1800,
        'vigente' => false,
    ])->assertSessionHasErrors('anio');
});

it('rechaza un año duplicado', function () {
    AnioLectivo::factory()->create(['anio' => 2026]);

    $this->post(route('academico.anios-lectivos.store'), [
        'activo' => true,
        'anio' => 2026,
        'vigente' => false,
    ])->assertSessionHasErrors('anio');
});

it('actualiza y audita un año lectivo', function () {
    $anioLectivo = AnioLectivo::factory()->create(['anio' => 2026, 'vigente' => false]);

    $this->put(route('academico.anios-lectivos.update', $anioLectivo), [
        'activo' => true,
        'anio' => 2026,
        'vigente' => true,
    ])->assertRedirect(route('academico.anios-lectivos.index'));

    expect($anioLectivo->refresh()->vigente)->toBeTrue();
    $this->assertDatabaseHas('logs', ['entidad' => 'anios_lectivos', 'entidad_id' => $anioLectivo->id, 'accion' => 'update']);
});

it('da de baja lógica y audita un año lectivo', function () {
    $anioLectivo = AnioLectivo::factory()->create(['activo' => true]);

    $this->patch(route('academico.anios-lectivos.desactivar', $anioLectivo))
        ->assertRedirect(route('academico.anios-lectivos.index'));

    expect($anioLectivo->refresh()->activo)->toBeFalse();
    $this->assertDatabaseHas('logs', ['entidad' => 'anios_lectivos', 'entidad_id' => $anioLectivo->id, 'accion' => 'deactivate']);
});
