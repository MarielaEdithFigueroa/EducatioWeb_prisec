<?php

use App\Models\Log;
use App\Models\TipoDocumentacionIntegrante;
use App\Models\User;
use Database\Seeders\TipoDocumentacionIntegranteSeeder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

dataset('catalogos integrantes', [
    'documentación' => [TipoDocumentacionIntegrante::class, 'academico.tipos_documentacion_integrante', 150, TipoDocumentacionIntegranteSeeder::class],
]);

it('protege todas las operaciones de los catálogos integrantes', function (string $modelo, string $ruta) {
    $registro = $modelo::factory()->create();

    $this->get(route($ruta.'.index'))->assertRedirect(route('login'));
    $this->post(route($ruta.'.store'), ['descripcion' => 'Nuevo'])->assertRedirect(route('login'));

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route($ruta.'.'.$accion, $registro), ['descripcion' => 'Cambio'])
            ->assertRedirect(route('login'));
    }

    expect($registro->fresh()->activo)->toBeTrue()
        ->and(Log::query()->count())->toBe(0);
})->with('catalogos integrantes');

it('lista activos e inactivos y define los campos del catálogo', function (string $modelo, string $ruta, int $longitud) {
    $inactivo = $modelo::factory()->create(['descripcion' => 'Anterior', 'activo' => false]);
    $activo = $modelo::factory()->create(['descripcion' => 'Vigente']);

    $this->actingAs(User::factory()->create())->get(route($ruta.'.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/catalogos/index')
            ->where('catalogo.longitud_descripcion', $longitud)
            ->where('catalogo.usa_orden', false)
            ->has('registros', 2)
            ->where('registros.0.id', $activo->id)
            ->where('registros.1.id', $inactivo->id)
            ->where('registros.1.activo', false)
        );
})->with('catalogos integrantes');

it('normaliza descripciones y guarda solo los campos permitidos', function (string $modelo, string $ruta) {
    $this->actingAs(User::factory()->create())
        ->post(route($ruta.'.store'), [
            'descripcion' => '  Descripción   de prueba  ',
            'activo' => false,
            'id' => 999,
        ])
        ->assertSessionHasNoErrors();

    $registro = $modelo::query()->sole();
    expect($registro->descripcion)->toBe('Descripción de prueba')
        ->and($registro->activo)->toBeTrue()
        ->and($registro->id)->not->toBe(999);

    $this->patch(route($ruta.'.update', $registro), [
        'descripcion' => 'Descripción actualizada',
        'activo' => false,
    ])->assertSessionHasNoErrors();

    expect($registro->fresh()->descripcion)->toBe('Descripción actualizada')
        ->and($registro->fresh()->activo)->toBeTrue();
})->with('catalogos integrantes');

it('rechaza descripciones vacías inválidas y demasiado largas', function (string $modelo, string $ruta, int $longitud) {
    $registro = $modelo::factory()->create();
    $this->actingAs(User::factory()->create());

    foreach (['   ', null, ['texto'], str_repeat('a', $longitud + 1)] as $descripcion) {
        $this->post(route($ruta.'.store'), ['descripcion' => $descripcion])
            ->assertSessionHasErrors('descripcion');
        $this->patch(route($ruta.'.update', $registro), ['descripcion' => $descripcion])
            ->assertSessionHasErrors('descripcion');
    }

    expect($modelo::query()->count())->toBe(1)
        ->and(Log::query()->count())->toBe(0);
})->with('catalogos integrantes');

it('rechaza duplicados incluso inactivos pero permite conservar la descripción propia', function (string $modelo, string $ruta) {
    $existente = $modelo::factory()->create(['descripcion' => 'Descripción existente', 'activo' => false]);
    $otro = $modelo::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->post(route($ruta.'.store'), ['descripcion' => '  Descripción   existente '])
        ->assertSessionHasErrors('descripcion');
    $this->patch(route($ruta.'.update', $otro), ['descripcion' => $existente->descripcion])
        ->assertSessionHasErrors('descripcion');
    $this->patch(route($ruta.'.update', $existente), ['descripcion' => $existente->descripcion])
        ->assertSessionHasNoErrors();

    expect($modelo::query()->count())->toBe(2)
        ->and($existente->fresh()->activo)->toBeFalse()
        ->and(Log::query()->count())->toBe(0);
})->with('catalogos integrantes');

it('no duplica auditoría al repetir la baja o reactivación', function (string $modelo, string $ruta) {
    $registro = $modelo::factory()->create();
    $usuario = User::factory()->create();
    $this->actingAs($usuario);

    foreach (['desactivar', 'desactivar', 'reactivar', 'reactivar'] as $accion) {
        $this->patch(route($ruta.'.'.$accion, $registro))->assertSessionHasNoErrors();
    }

    $logs = Log::query()->where('entidad', $registro->getTable())->orderBy('id')->get();
    expect($registro->fresh()->activo)->toBeTrue()
        ->and($logs)->toHaveCount(2)
        ->and($logs[0]->accion)->toBe('deactivate')
        ->and($logs[0]->anterior['activo'])->toBeTrue()
        ->and($logs[0]->nuevo['activo'])->toBeFalse()
        ->and($logs[1]->accion)->toBe('reactivate')
        ->and($logs[1]->nuevo['activo'])->toBeTrue()
        ->and($logs[1]->usuario_id)->toBe($usuario->id);
})->with('catalogos integrantes');

it('respeta la autorización centralizada en todos los endpoints', function (string $modelo, string $ruta) {
    $registro = $modelo::factory()->create();
    $this->actingAs(User::factory()->create());
    Gate::before(fn (): bool => false);

    $this->get(route($ruta.'.index'))->assertForbidden();
    $this->post(route($ruta.'.store'), ['descripcion' => 'Nuevo'])->assertForbidden();

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route($ruta.'.'.$accion, $registro), ['descripcion' => 'Cambio'])
            ->assertForbidden();
    }

    expect($registro->fresh()->activo)->toBeTrue()
        ->and(Log::query()->count())->toBe(0);
})->with('catalogos integrantes');

it('revierte la operación del catálogo integrante si falla la auditoría', function (string $modelo, string $ruta) {
    $registro = $modelo::factory()->create();
    $descripcion = $registro->descripcion;
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('Fallo de auditoría');
    });

    expect(fn () => $this->post(route($ruta.'.store'), ['descripcion' => 'Nuevo']))
        ->toThrow(RuntimeException::class, 'Fallo de auditoría');
    expect(fn () => $this->patch(route($ruta.'.update', $registro), ['descripcion' => 'Cambio']))
        ->toThrow(RuntimeException::class, 'Fallo de auditoría');
    expect(fn () => $this->patch(route($ruta.'.desactivar', $registro)))
        ->toThrow(RuntimeException::class, 'Fallo de auditoría');

    expect($modelo::query()->count())->toBe(1)
        ->and($registro->fresh()->descripcion)->toBe($descripcion)
        ->and($registro->fresh()->activo)->toBeTrue();
})->with('catalogos integrantes');

it('carga los catálogos iniciales sin duplicarlos ni reactivar bajas', function (string $modelo, string $ruta, int $longitud, string $seeder) {
    $this->seed($seeder);
    $cantidad = $modelo::query()->count();
    $registro = $modelo::query()->firstOrFail();
    $registro->update(['activo' => false]);

    $this->seed($seeder);

    expect($cantidad)->toBeGreaterThan(0)
        ->and($modelo::query()->count())->toBe($cantidad)
        ->and($registro->fresh()->activo)->toBeFalse();
})->with('catalogos integrantes');
