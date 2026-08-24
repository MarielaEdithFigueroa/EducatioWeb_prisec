<?php

use App\Models\Ciudad;
use App\Models\Log;
use App\Models\Provincia;
use App\Models\User;
use Database\Seeders\ProvinciasYCiudadesSeeder;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

it('protege el ABM de localidades', function () {
    $this->get(route('sistema.localidades.index'))
        ->assertRedirect(route('login'));
});

it('permite administrar localidades con baja lógica y auditoría', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);
    $usuario = User::factory()->create();
    $neuquen = Provincia::query()->where('codigo', 'NQN')->sole();
    $buenosAires = Provincia::query()->where('codigo', 'BA')->sole();

    $this->actingAs($usuario)
        ->get(route('sistema.localidades.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sistema/localidades/index')
            ->has('localidades', 44)
            ->has('provincias', 25)
        );

    $this->actingAs($usuario)
        ->from(route('sistema.localidades.index'))
        ->post(route('sistema.localidades.store'), [
            'provincia_id' => $neuquen->id,
            'nombre' => '  Nueva   localidad  ',
            'codigo_postal' => ' q8300 xyz ',
        ])
        ->assertRedirect(route('sistema.localidades.index'))
        ->assertSessionHasNoErrors();

    $localidad = Ciudad::query()
        ->whereBelongsTo($neuquen)
        ->where('nombre', 'Nueva localidad')
        ->sole();

    expect($localidad->activo)->toBeTrue()
        ->and($localidad->codigo_postal)->toBe('Q8300 XYZ');

    $logCreacion = Log::query()
        ->where('entidad', $localidad->getTable())
        ->where('entidad_id', $localidad->id)
        ->where('accion', 'create')
        ->sole();

    expect($logCreacion->anterior)->toBeNull()
        ->and($logCreacion->nuevo['nombre'])->toBe('Nueva localidad')
        ->and($logCreacion->usuario_id)->toBe($usuario->id);

    $this->actingAs($usuario)
        ->from(route('sistema.localidades.index'))
        ->patch(route('sistema.localidades.update', $localidad), [
            'provincia_id' => $buenosAires->id,
            'nombre' => 'Nueva localidad bonaerense',
            'codigo_postal' => '',
        ])
        ->assertRedirect(route('sistema.localidades.index'))
        ->assertSessionHasNoErrors();

    $localidad->refresh();

    expect($localidad->provincia_id)->toBe($buenosAires->id)
        ->and($localidad->nombre)->toBe('Nueva localidad bonaerense')
        ->and($localidad->codigo_postal)->toBeNull()
        ->and(Log::query()
            ->where('entidad', $localidad->getTable())
            ->where('entidad_id', $localidad->id)
            ->where('accion', 'update')
            ->exists())
        ->toBeTrue();

    $this->actingAs($usuario)
        ->patch(route('sistema.localidades.desactivar', $localidad))
        ->assertRedirect();

    expect($localidad->refresh()->activo)->toBeFalse()
        ->and(Log::query()
            ->where('entidad', $localidad->getTable())
            ->where('entidad_id', $localidad->id)
            ->where('accion', 'deactivate')
            ->exists())
        ->toBeTrue();

    $this->actingAs($usuario)
        ->patch(route('sistema.localidades.reactivar', $localidad))
        ->assertRedirect();

    expect($localidad->refresh()->activo)->toBeTrue()
        ->and(Log::query()
            ->where('entidad', $localidad->getTable())
            ->where('entidad_id', $localidad->id)
            ->where('accion', 'reactivate')
            ->exists())
        ->toBeTrue();
});

it('valida los datos y la unicidad del nombre dentro de cada provincia', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);
    $usuario = User::factory()->create();
    $neuquen = Provincia::query()->where('codigo', 'NQN')->sole();
    $buenosAires = Provincia::query()->where('codigo', 'BA')->sole();

    $this->actingAs($usuario)
        ->post(route('sistema.localidades.store'), [])
        ->assertSessionHasErrors(['provincia_id', 'nombre']);

    $this->actingAs($usuario)
        ->post(route('sistema.localidades.store'), [
            'provincia_id' => $neuquen->id,
            'nombre' => 'Neuquén',
            'codigo_postal' => null,
        ])
        ->assertSessionHasErrors('nombre');

    $this->actingAs($usuario)
        ->post(route('sistema.localidades.store'), [
            'provincia_id' => $buenosAires->id,
            'nombre' => 'Neuquén',
            'codigo_postal' => null,
        ])
        ->assertSessionHasNoErrors();

    expect(Ciudad::query()
        ->whereBelongsTo($buenosAires)
        ->where('nombre', 'Neuquén')
        ->exists())
        ->toBeTrue();

    $this->actingAs($usuario)
        ->post(route('sistema.localidades.store'), [
            'provincia_id' => 999,
            'nombre' => 'Localidad sin provincia',
            'codigo_postal' => '12345678901',
        ])
        ->assertSessionHasErrors(['provincia_id', 'codigo_postal']);
});

it('impide nuevas asociaciones con provincias inactivas y conserva las históricas', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);
    $usuario = User::factory()->create();
    $neuquen = Provincia::query()->where('codigo', 'NQN')->sole();
    $localidad = Ciudad::query()
        ->whereBelongsTo($neuquen)
        ->where('nombre', 'Neuquén')
        ->sole();

    $neuquen->update(['activo' => false]);

    $this->actingAs($usuario)
        ->get(route('sistema.localidades.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('provincias', fn ($provincias) => $provincias->contains(
                fn (array $provincia): bool => $provincia['id'] === $neuquen->id
                    && $provincia['activo'] === false,
            ))
        );

    $this->actingAs($usuario)
        ->post(route('sistema.localidades.store'), [
            'provincia_id' => $neuquen->id,
            'nombre' => 'Nueva en provincia inactiva',
            'codigo_postal' => null,
        ])
        ->assertSessionHasErrors('provincia_id');

    $this->actingAs($usuario)
        ->patch(route('sistema.localidades.update', $localidad), [
            'provincia_id' => $neuquen->id,
            'nombre' => $localidad->nombre,
            'codigo_postal' => '8300',
        ])
        ->assertSessionHasNoErrors();

    expect($localidad->refresh()->codigo_postal)->toBe('8300');
});

it('revierte el alta cuando falla la auditoría', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);
    $usuario = User::factory()->create();
    $neuquen = Provincia::query()->where('codigo', 'NQN')->sole();
    $this->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('No se pudo escribir el log.');
    });

    expect(fn () => $this->actingAs($usuario)->post(
        route('sistema.localidades.store'),
        [
            'provincia_id' => $neuquen->id,
            'nombre' => 'No debe persistir',
            'codigo_postal' => null,
        ],
    ))->toThrow(RuntimeException::class, 'No se pudo escribir el log.');

    expect(Ciudad::query()->where('nombre', 'No debe persistir')->exists())
        ->toBeFalse();
});
