<?php

use App\EstadoAnioLectivo;
use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('requiere autenticación para administrar la estructura académica', function (string $metodo, string $ruta) {
    $this->{$metodo}($ruta)->assertRedirect(route('login'));
})->with([
    'listado' => ['get', '/academico/estructura'],
    'años' => ['post', '/academico/anios-lectivos'],
    'cursos' => ['post', '/academico/cursos'],
    'divisiones' => ['post', '/academico/divisiones'],
    'turnos' => ['post', '/academico/turnos'],
    'planes' => ['post', '/academico/planes-estudio'],
]);

it('muestra la estructura académica a un usuario autenticado', function () {
    $nivel = Nivel::factory()->create();
    AnioLectivo::factory()->create(['anio' => 2026]);
    Curso::factory()->for($nivel)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('academico.estructura.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/estructura')
            ->has('niveles', 1)
            ->has('aniosLectivos', 1)
            ->has('cursos', 1));
});

it('crea los catálogos académicos y registra la auditoría', function () {
    $usuario = User::factory()->create();
    $nivel = Nivel::factory()->create();

    $this->actingAs($usuario)->post(route('academico.anios-lectivos.store'), ['anio' => 2027])->assertRedirect();
    $this->post(route('academico.cursos.store'), ['nivel_id' => $nivel->id, 'descripcion' => 'Primer grado', 'orden' => 1])->assertRedirect();
    $this->post(route('academico.divisiones.store'), ['nivel_id' => $nivel->id, 'descripcion' => 'A', 'orden' => 1])->assertRedirect();
    $this->post(route('academico.turnos.store'), ['nivel_id' => $nivel->id, 'descripcion' => 'Mañana', 'orden' => 1])->assertRedirect();
    $this->post(route('academico.planes-estudio.store'), ['nivel_id' => $nivel->id, 'codigo' => ' ing ', 'descripcion' => 'Ingresantes'])->assertRedirect();

    expect(AnioLectivo::query()->first()->estado)->toBe(EstadoAnioLectivo::Preparacion);
    $this->assertDatabaseHas('cursos', ['descripcion' => 'Primer grado']);
    $this->assertDatabaseHas('divisiones', ['descripcion' => 'A']);
    $this->assertDatabaseHas('turnos', ['descripcion' => 'Mañana']);
    $this->assertDatabaseHas('planes_estudio', ['codigo' => 'ING']);
    $this->assertDatabaseCount('logs', 5);
});

it('valida unicidad por nivel y permite la misma descripción en otro nivel', function () {
    $usuario = User::factory()->create();
    $primaria = Nivel::factory()->create();
    $secundaria = Nivel::factory()->create();
    Curso::factory()->for($primaria)->create(['descripcion' => 'Primero']);

    $this->actingAs($usuario)
        ->post(route('academico.cursos.store'), ['nivel_id' => $primaria->id, 'descripcion' => 'Primero', 'orden' => 2])
        ->assertSessionHasErrors('descripcion');

    $this->post(route('academico.cursos.store'), ['nivel_id' => $secundaria->id, 'descripcion' => 'Primero', 'orden' => 1])
        ->assertSessionHasNoErrors();
});

it('actualiza, desactiva y reactiva un catálogo con auditoría', function () {
    $usuario = User::factory()->create();
    $curso = Curso::factory()->create();

    $this->actingAs($usuario)->patch(route('academico.cursos.update', $curso), [
        'nivel_id' => $curso->nivel_id,
        'descripcion' => 'Curso actualizado',
        'orden' => 3,
    ])->assertRedirect();
    $this->patch(route('academico.cursos.desactivar', $curso))->assertRedirect();
    $this->patch(route('academico.cursos.reactivar', $curso))->assertRedirect();

    expect($curso->refresh()->activo)->toBeTrue()
        ->and($curso->descripcion)->toBe('Curso actualizado');
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'entidad_id' => $curso->id, 'accion' => 'update']);
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'entidad_id' => $curso->id, 'accion' => 'deactivate']);
    $this->assertDatabaseHas('logs', ['entidad' => 'cursos', 'entidad_id' => $curso->id, 'accion' => 'reactivate']);
});

it('mantiene un único año vigente y cierra el anterior', function () {
    $usuario = User::factory()->create();
    $anterior = AnioLectivo::factory()->vigente()->create(['anio' => 2026]);
    $nuevo = AnioLectivo::factory()->create(['anio' => 2027]);

    $this->actingAs($usuario)
        ->patch(route('academico.anios-lectivos.marcar-vigente', $nuevo))
        ->assertRedirect();

    expect($anterior->refresh()->estado)->toBe(EstadoAnioLectivo::Cerrado)
        ->and($nuevo->refresh()->estado)->toBe(EstadoAnioLectivo::Vigente)
        ->and(AnioLectivo::query()->where('estado', EstadoAnioLectivo::Vigente)->count())->toBe(1);
    $this->assertDatabaseCount('logs', 2);
});

it('no permite desactivar el año vigente ni volver vigente un año cerrado', function () {
    $usuario = User::factory()->create();
    $vigente = AnioLectivo::factory()->vigente()->create();
    $cerrado = AnioLectivo::factory()->cerrado()->create();

    $this->actingAs($usuario)
        ->patch(route('academico.anios-lectivos.desactivar', $vigente))
        ->assertSessionHasErrors('anio');
    $this->patch(route('academico.anios-lectivos.marcar-vigente', $cerrado))
        ->assertSessionHasErrors('anio');

    expect($vigente->refresh()->activo)->toBeTrue()
        ->and($cerrado->refresh()->estado)->toBe(EstadoAnioLectivo::Cerrado);
});

it('no reemplaza el año vigente por uno anterior ni permite renumerar historia', function () {
    $usuario = User::factory()->create();
    $vigente = AnioLectivo::factory()->vigente()->create(['anio' => 2027]);
    $anterior = AnioLectivo::factory()->create(['anio' => 2026]);
    $nivel = Nivel::factory()->create();
    $grupo = Grupo::factory()->paraNivel($nivel)->create(['anio_lectivo_id' => $anterior->id]);

    $this->actingAs($usuario)
        ->patch(route('academico.anios-lectivos.marcar-vigente', $anterior))
        ->assertSessionHasErrors('anio');
    $this->patch(route('academico.anios-lectivos.update', $anterior), ['anio' => 2025])
        ->assertSessionHasErrors('anio');

    expect($vigente->refresh()->estado)->toBe(EstadoAnioLectivo::Vigente)
        ->and($anterior->refresh()->anio)->toBe(2026)
        ->and($grupo->exists)->toBeTrue();
});
