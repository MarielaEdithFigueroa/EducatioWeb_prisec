<?php

use App\Models\Curso;
use App\Models\Division;
use App\Models\Log;
use App\Models\Nivel;
use App\Models\PlanEstudio;
use App\Models\Turno;
use App\Models\User;
use Database\Seeders\NivelesSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

dataset('catalogos academicos', [
    'niveles' => [[
        'clave' => 'niveles',
        'modelo' => Nivel::class,
        'ruta' => 'academico.niveles',
        'datos' => ['codigo' => 'TECNICA', 'descripcion' => 'Técnica'],
        'descripcion_actualizada' => 'Técnica actualizada',
    ]],
    'planes de estudio' => [[
        'clave' => 'planes_estudio',
        'modelo' => PlanEstudio::class,
        'ruta' => 'academico.planes_estudio',
        'datos' => ['descripcion' => 'Plan experimental', 'orden' => 8],
        'descripcion_actualizada' => 'Plan experimental actualizado',
        'usa_nivel' => true,
    ]],
    'turnos' => [[
        'clave' => 'turnos',
        'modelo' => Turno::class,
        'ruta' => 'academico.turnos',
        'datos' => ['descripcion' => 'Vespertino', 'orden' => 3],
        'descripcion_actualizada' => 'Vespertino actualizado',
    ]],
    'cursos' => [[
        'clave' => 'cursos',
        'modelo' => Curso::class,
        'ruta' => 'academico.cursos',
        'datos' => ['descripcion' => 'Curso experimental', 'orden' => 8],
        'descripcion_actualizada' => 'Curso experimental actualizado',
        'usa_nivel' => true,
    ]],
    'divisiones' => [[
        'clave' => 'divisiones',
        'modelo' => Division::class,
        'ruta' => 'academico.divisiones',
        'datos' => ['descripcion' => 'C', 'orden' => 3],
        'descripcion_actualizada' => 'C actualizada',
    ]],
]);

it('protege el índice de cada catálogo', function (array $catalogo) {
    $this->get(route($catalogo['ruta'].'.index'))
        ->assertRedirect(route('login'));
})->with('catalogos academicos');

it('permite administrar cada catálogo con baja lógica y auditoría', function (array $catalogo) {
    $this->seed(NivelesSeeder::class);
    $usuario = User::factory()->create();
    $nivel = Nivel::query()->where('codigo', 'PRIMARIA')->sole();
    $datos = $catalogo['datos'];

    if ($catalogo['usa_nivel'] ?? false) {
        $datos['nivel_id'] = $nivel->id;
    }

    $this->actingAs($usuario)
        ->get(route($catalogo['ruta'].'.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/catalogos/index')
            ->where('catalogo.clave', $catalogo['clave'])
            ->has('registros')
        );

    $this->actingAs($usuario)
        ->from(route($catalogo['ruta'].'.index'))
        ->post(route($catalogo['ruta'].'.store'), $datos)
        ->assertRedirect(route($catalogo['ruta'].'.index'))
        ->assertSessionHasNoErrors();

    /** @var class-string<Model> $modelo */
    $modelo = $catalogo['modelo'];
    $registro = $modelo::query()
        ->where('descripcion', $datos['descripcion'])
        ->sole();

    expect((bool) $registro->getAttribute('activo'))->toBeTrue();

    $logCreacion = Log::query()
        ->where('entidad', $registro->getTable())
        ->where('entidad_id', $registro->getKey())
        ->where('accion', 'create')
        ->sole();

    expect($logCreacion->anterior)->toBeNull()
        ->and($logCreacion->nuevo['descripcion'])->toBe($datos['descripcion'])
        ->and($logCreacion->usuario_id)->toBe($usuario->id);

    $datosActualizados = [
        ...$datos,
        'descripcion' => $catalogo['descripcion_actualizada'],
    ];

    if ($catalogo['clave'] === 'niveles') {
        $datosActualizados['codigo'] = 'TECNICA_ACT';
    }

    $this->actingAs($usuario)
        ->from(route($catalogo['ruta'].'.index'))
        ->patch(route($catalogo['ruta'].'.update', $registro), $datosActualizados)
        ->assertRedirect(route($catalogo['ruta'].'.index'))
        ->assertSessionHasNoErrors();

    $registro->refresh();

    expect($registro->getAttribute('descripcion'))
        ->toBe($catalogo['descripcion_actualizada']);

    $logActualizacion = Log::query()
        ->where('entidad', $registro->getTable())
        ->where('entidad_id', $registro->getKey())
        ->where('accion', 'update')
        ->sole();

    expect($logActualizacion->anterior['descripcion'])->toBe($datos['descripcion'])
        ->and($logActualizacion->nuevo['descripcion'])
        ->toBe($catalogo['descripcion_actualizada']);

    $this->actingAs($usuario)
        ->from(route($catalogo['ruta'].'.index'))
        ->patch(route($catalogo['ruta'].'.desactivar', $registro))
        ->assertRedirect(route($catalogo['ruta'].'.index'));

    expect((bool) $registro->refresh()->getAttribute('activo'))->toBeFalse()
        ->and($modelo::query()->whereKey($registro->getKey())->exists())->toBeTrue()
        ->and(Log::query()
            ->where('entidad', $registro->getTable())
            ->where('entidad_id', $registro->getKey())
            ->where('accion', 'deactivate')
            ->exists())
        ->toBeTrue();

    $this->actingAs($usuario)
        ->from(route($catalogo['ruta'].'.index'))
        ->patch(route($catalogo['ruta'].'.reactivar', $registro))
        ->assertRedirect(route($catalogo['ruta'].'.index'));

    expect((bool) $registro->refresh()->getAttribute('activo'))->toBeTrue()
        ->and(Log::query()
            ->where('entidad', $registro->getTable())
            ->where('entidad_id', $registro->getKey())
            ->where('accion', 'reactivate')
            ->exists())
        ->toBeTrue();
})->with('catalogos academicos');

it('valida en servidor los datos obligatorios de cada catálogo', function (array $catalogo) {
    $this->actingAs(User::factory()->create())
        ->post(route($catalogo['ruta'].'.store'), [])
        ->assertSessionHasErrors('descripcion');
})->with('catalogos academicos');

it('revierte el alta si no puede registrar la auditoría', function () {
    $usuario = User::factory()->create();
    $this->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('No se pudo escribir el log.');
    });

    expect(fn () => $this->actingAs($usuario)->post(
        route('academico.turnos.store'),
        ['descripcion' => 'No debe persistir', 'orden' => 9],
    ))->toThrow(RuntimeException::class, 'No se pudo escribir el log.');

    expect(Turno::query()->where('descripcion', 'No debe persistir')->exists())
        ->toBeFalse();
});
