<?php

use App\Models\Aula;
use App\Models\Log;
use App\Models\User;
use Database\Seeders\AulaSeeder;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

function datosAula(Aula $aula): array
{
    return $aula->only(['descripcion', 'descripcion_larga', 'capacidad', 'es_especial', 'especialidad']);
}

it('protege todas las acciones de aulas con autenticación', function () {
    $aula = Aula::factory()->create();

    foreach (['index', 'create', 'show', 'edit'] as $accion) {
        $this->get(route('academico.aulas.'.$accion, in_array($accion, ['show', 'edit']) ? $aula : []))
            ->assertRedirect(route('login'));
    }

    $this->post(route('academico.aulas.store'), datosAula($aula))->assertRedirect(route('login'));

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.aulas.'.$accion, $aula), datosAula($aula))->assertRedirect(route('login'));
    }
});

it('lista aulas por descripción incluyendo inactivas y presenta alta consulta y edición', function () {
    $segunda = Aula::factory()->especial()->create(['descripcion' => 'Sala de música', 'activo' => false]);
    $primera = Aula::factory()->create(['descripcion' => 'Aula 1']);
    $this->actingAs(User::factory()->create());

    $this->get(route('academico.aulas.index'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('academico/aulas/index')->has('aulas', 2)
        ->where('aulas.0.id', $primera->id)->where('aulas.1.id', $segunda->id)
        ->where('aulas.1.activo', false)->where('aulas.1.especialidad', 'Música'));

    $this->get(route('academico.aulas.create'))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('academico/aulas/formulario')->where('aula', null));

    foreach (['show' => 'show', 'edit' => 'formulario'] as $accion => $pagina) {
        $this->get(route('academico.aulas.'.$accion, $segunda))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('academico/aulas/'.$pagina)->where('aula.id', $segunda->id)
            ->where('aula.es_especial', true)->where('aula.especialidad', 'Música'));
    }
});

it('crea un aula común con datos mínimos y auditoría sin permitir elegir estado', function () {
    $usuario = User::factory()->create();

    $this->actingAs($usuario)->post(route('academico.aulas.store'), [
        'descripcion' => '  Aula   2  ',
        'es_especial' => false,
        'especialidad' => 'Dato que no corresponde',
        'activo' => false,
    ])->assertSessionHasNoErrors()->assertRedirect(route('academico.aulas.index'));

    $aula = Aula::query()->sole();
    expect($aula->descripcion)->toBe('Aula 2')
        ->and($aula->descripcion_larga)->toBeNull()
        ->and($aula->capacidad)->toBeNull()
        ->and($aula->especialidad)->toBeNull()
        ->and($aula->es_especial)->toBeFalse()
        ->and($aula->activo)->toBeTrue();

    $log = Log::query()->where('entidad', 'aulas')->sole();
    expect($log->accion)->toBe('create')->and($log->anterior)->toBeNull()
        ->and($log->nuevo['descripcion'])->toBe('Aula 2')
        ->and($log->usuario_id)->toBe($usuario->id);
});

it('crea un aula especial con texto libre y descripción larga', function () {
    $this->actingAs(User::factory()->create())->post(route('academico.aulas.store'), [
        'descripcion' => 'Aula 2',
        'descripcion_larga' => "Edificio viejo, primer piso\nAcceso por escalera",
        'capacidad' => '25',
        'es_especial' => true,
        'especialidad' => '  Música   e instrumentos  ',
    ])->assertSessionHasNoErrors();

    $aula = Aula::query()->sole();
    expect($aula->es_especial)->toBeTrue()->and($aula->capacidad)->toBe(25)
        ->and($aula->especialidad)->toBe('Música e instrumentos')
        ->and($aula->descripcion_larga)->toBe("Edificio viejo, primer piso\nAcceso por escalera");
});

it('valida campos y capacidad en servidor', function (string $campo, mixed $valor) {
    $datos = datosAula(Aula::factory()->especial()->make());
    $this->actingAs(User::factory()->create())->post(route('academico.aulas.store'), [...$datos, $campo => $valor])
        ->assertSessionHasErrors($campo);
    expect(Aula::query()->count())->toBe(0);
})->with([
    ['descripcion', ''], ['descripcion', '   '], ['descripcion', str_repeat('a', 101)],
    ['descripcion', ['valor']], ['descripcion_larga', str_repeat('a', 2001)],
    ['capacidad', 0], ['capacidad', -1], ['capacidad', 65536], ['capacidad', '2.5'], ['capacidad', 'veinte'],
    ['es_especial', 'cualquier texto'], ['es_especial', null],
    ['especialidad', null], ['especialidad', '   '], ['especialidad', str_repeat('a', 101)],
]);

it('no permite duplicar descripciones aunque el aula esté inactiva', function (bool $activo) {
    Aula::factory()->create(['descripcion' => 'Aula 2', 'activo' => $activo]);
    $this->actingAs(User::factory()->create())->post(route('academico.aulas.store'), [
        'descripcion' => ' Aula  2 ', 'es_especial' => false,
    ])->assertSessionHasErrors('descripcion');
    expect(Aula::query()->count())->toBe(1);
})->with([true, false]);

it('limpia especialidad al pasar a común y audita sólo cambios reales sin reactivar', function () {
    $aula = Aula::factory()->especial()->create(['activo' => false]);
    $datos = [...datosAula($aula), 'es_especial' => false, 'especialidad' => 'Música', 'activo' => true];
    $this->actingAs(User::factory()->create());

    for ($i = 0; $i < 2; $i++) {
        $this->patch(route('academico.aulas.update', $aula), $datos)->assertSessionHasNoErrors()
            ->assertRedirect(route('academico.aulas.show', $aula));
    }

    expect($aula->refresh()->es_especial)->toBeFalse()
        ->and($aula->especialidad)->toBeNull()->and($aula->activo)->toBeFalse();

    $log = Log::query()->where('entidad', 'aulas')->sole();
    expect($log->accion)->toBe('update')->and($log->anterior['especialidad'])->toBe('Música')
        ->and($log->nuevo['especialidad'])->toBeNull();
});

it('exige especialidad al pasar de aula común a especial y permite vaciar datos opcionales', function () {
    $aula = Aula::factory()->create(['descripcion_larga' => 'Edificio viejo', 'capacidad' => 30]);
    $datos = [...datosAula($aula), 'es_especial' => true, 'especialidad' => null];
    $this->actingAs(User::factory()->create())->patch(route('academico.aulas.update', $aula), $datos)
        ->assertSessionHasErrors('especialidad');

    $this->patch(route('academico.aulas.update', $aula), [
        ...$datos, 'especialidad' => 'Laboratorio', 'descripcion_larga' => '', 'capacidad' => '',
    ])->assertSessionHasNoErrors();

    expect($aula->refresh()->especialidad)->toBe('Laboratorio')
        ->and($aula->es_especial)->toBeTrue()
        ->and($aula->descripcion_larga)->toBeNull()
        ->and($aula->capacidad)->toBeNull();
});

it('impide modificar el nombre para duplicar otra aula', function () {
    $aula = Aula::factory()->create(['descripcion' => 'Aula 1']);
    $otra = Aula::factory()->create(['descripcion' => 'Aula 2']);
    $this->actingAs(User::factory()->create())->patch(route('academico.aulas.update', $aula), [
        ...datosAula($aula), 'descripcion' => $otra->descripcion,
    ])->assertSessionHasErrors('descripcion');

    expect($aula->refresh()->descripcion)->toBe('Aula 1');
});

it('da de baja y reactiva sin borrar datos ni duplicar auditoría', function () {
    $aula = Aula::factory()->especial()->create();
    $datos = datosAula($aula);
    $this->actingAs(User::factory()->create());

    foreach (['desactivar' => false, 'reactivar' => true] as $accion => $activo) {
        for ($i = 0; $i < 2; $i++) {
            $this->from(route('academico.aulas.index'))->patch(route('academico.aulas.'.$accion, $aula))
                ->assertRedirect(route('academico.aulas.index'));
        }

        expect($aula->refresh()->activo)->toBe($activo)
            ->and(datosAula($aula))->toBe($datos);
    }

    expect(Aula::query()->count())->toBe(1)
        ->and(Log::query()->where('entidad', 'aulas')->orderBy('id')->pluck('accion')->all())
        ->toBe(['deactivate', 'reactivate']);
});

it('carga ejemplos sin duplicarlos ni sobrescribir cambios de usuario', function () {
    $this->seed(AulaSeeder::class);
    $aula = Aula::query()->where('descripcion', 'Aula 2')->sole();
    $aula->update(['capacidad' => 18, 'activo' => false]);
    $this->seed(AulaSeeder::class);

    expect(Aula::query()->count())->toBe(3)
        ->and($aula->refresh()->capacidad)->toBe(18)->and($aula->activo)->toBeFalse()
        ->and(Aula::query()->where('es_especial', true)->sole()->especialidad)->toBe('Música');
});

it('revierte el alta si falla la auditoría', function () {
    $datos = datosAula(Aula::factory()->make());
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('Auditoría no disponible.');
    });

    expect(fn () => $this->post(route('academico.aulas.store'), $datos))
        ->toThrow(RuntimeException::class, 'Auditoría no disponible.');
    expect(Aula::query()->count())->toBe(0);
});
