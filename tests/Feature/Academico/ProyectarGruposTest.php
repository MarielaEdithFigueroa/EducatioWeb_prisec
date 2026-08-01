<?php

use App\Models\AnioLectivo;
use App\Models\Curso;
use App\Models\Division;
use App\Models\Grupo;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use App\Models\User;

it('proyecta grupos de forma idempotente y preserva los grupos manuales', function () {
    $nivel = Nivel::factory()->create();
    $origen = AnioLectivo::factory()->vigente()->create(['anio' => 2026]);
    $destino = AnioLectivo::factory()->create(['anio' => 2027]);
    $plan = PlanEstudio::factory()->for($nivel)->create();
    $curso = Curso::factory()->for($nivel)->create();
    $turno = Turno::factory()->for($nivel)->create();
    $divisiones = Division::factory()->count(5)->for($nivel)->sequence(
        ['descripcion' => 'A'],
        ['descripcion' => 'B'],
        ['descripcion' => 'C'],
        ['descripcion' => 'D'],
        ['descripcion' => 'E'],
    )->create();

    $datos = fn (AnioLectivo $anio, Division $division): array => [
        'anio_lectivo_id' => $anio->id,
        'plan_estudio_id' => $plan->id,
        'curso_id' => $curso->id,
        'division_id' => $division->id,
        'turno_id' => $turno->id,
    ];

    Grupo::factory()->create($datos($origen, $divisiones[0]));
    Grupo::factory()->create($datos($origen, $divisiones[1]));
    Grupo::factory()->create($datos($origen, $divisiones[2]));
    Grupo::factory()->inactivo()->create($datos($origen, $divisiones[3]));
    $reactivable = Grupo::factory()->inactivo()->create($datos($destino, $divisiones[0]));
    Grupo::factory()->create($datos($destino, $divisiones[2]));
    $manual = Grupo::factory()->create($datos($destino, $divisiones[4]));

    $usuario = User::factory()->create();
    $ruta = route('academico.anios-lectivos.proyectar-grupos', $origen);

    $this->actingAs($usuario)->post($ruta, ['destino_id' => $destino->id])->assertRedirect();

    expect($reactivable->refresh()->activo)->toBeTrue()
        ->and($manual->refresh()->activo)->toBeTrue();
    $this->assertDatabaseHas('grupos', $datos($destino, $divisiones[1]));
    $this->assertDatabaseMissing('grupos', $datos($destino, $divisiones[3]));
    $this->assertDatabaseCount('grupos', 8);

    $this->post($ruta, ['destino_id' => $destino->id])->assertRedirect();
    $this->assertDatabaseCount('grupos', 8);
    $this->assertDatabaseHas('logs', ['entidad' => 'grupos', 'entidad_id' => $reactivable->id, 'accion' => 'reactivate']);
});

it('exige un destino posterior, activo y en preparación', function () {
    $usuario = User::factory()->create();
    $origen = AnioLectivo::factory()->vigente()->create(['anio' => 2027]);
    $anterior = AnioLectivo::factory()->create(['anio' => 2026]);
    $cerrado = AnioLectivo::factory()->cerrado()->create(['anio' => 2028]);
    $ruta = route('academico.anios-lectivos.proyectar-grupos', $origen);

    $this->actingAs($usuario)->post($ruta, ['destino_id' => $origen->id])->assertSessionHasErrors('destino_id');
    $this->post($ruta, ['destino_id' => $anterior->id])->assertSessionHasErrors('destino_id');
    $this->post($ruta, ['destino_id' => $cerrado->id])->assertSessionHasErrors('destino_id');
});
