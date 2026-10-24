<?php

use App\Models\Alumno;
use App\Models\Integrante;
use App\Models\Responsable;
use App\Models\TipoVinculo;
use App\Models\User;
use Database\Seeders\TiposVinculoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

it('normaliza el celular del integrante ingresado en formato nacional', function () {
    $this->actingAs(User::factory()->create())->withoutExceptionHandling()->post(route('academico.integrantes.store'), [
        'legajo' => 'TEL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana', 'celular' => '2994167362',
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect(Integrante::query()->sole()->celular)->toBe('+5492994167362');
});

it('aplica el tipo de línea a todos los teléfonos del legajo', function (string $campo, bool $fijo, string $esperado) {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), [
        'legajo' => 'TEL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana', $campo => '299 5123456', $campo.'_es_fijo' => $fijo,
    ])->assertSessionHasNoErrors();
    $integrante = Integrante::query()->sole();
    expect($integrante->{$campo})->toBe($esperado)->and($integrante->getAttributes())->not->toHaveKey($campo.'_es_fijo');
})->with(Integrante::TELEFONOS_CON_TIPO)->with([
    'móvil' => [false, '+5492995123456'], 'fijo' => [true, '+542995123456'],
]);

it('conserva los teléfonos fijos al editar sin cambiar el tipo', function () {
    $integrante = Integrante::factory()->create(['telefono' => '+542995123456']);
    $this->actingAs(User::factory()->create())->get(route('academico.integrantes.edit', $integrante))
        ->assertInertia(fn (Assert $page) => $page->where('integrante.telefono_es_fijo', true));
    $this->patch(route('academico.integrantes.update', $integrante), [
        'legajo' => $integrante->legajo, 'apellido' => $integrante->apellido, 'nombre' => $integrante->nombre,
        'telefono' => $integrante->telefono,
    ])->assertSessionHasNoErrors();
    expect($integrante->fresh()->telefono)->toBe('+542995123456');
});

it('rechaza teléfonos incorrectos y tipos contradictorios sin convertirlos a vacío', function (mixed $telefono, mixed $fijo, string $error) {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), [
        'legajo' => 'TEL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana', 'telefono' => $telefono, 'telefono_es_fijo' => $fijo,
    ])->assertSessionHasErrors($error);
    expect(Integrante::query()->count())->toBe(0);
})->with([
    'corto' => ['299416', false, 'telefono'],
    'texto' => ['no es un teléfono', false, 'telefono'],
    'array' => [['2995123456'], false, 'telefono'],
    'extensión' => ['299 5123456 interno 12', false, 'telefono'],
    'móvil como fijo' => ['+5492995123456', true, 'telefono'],
    'tipo inválido' => ['2995123456', 'quizás', 'telefono_es_fijo'],
]);

it('normaliza responsables preservando el original y el tipo de línea al editar', function () {
    $responsable = Responsable::query()->create(['apellido' => 'Pérez', 'nombre' => 'Ana']);
    $this->actingAs(User::factory()->create())->patch(route('academico.responsables.update', $responsable), ['responsable' => [
        'apellido' => 'Pérez', 'nombre' => 'Ana', 'telefono' => '0299 15 5123456',
        'telefono_laboral' => '0299 4515123', 'telefono_laboral_es_fijo' => true,
    ]])->assertSessionHasNoErrors();
    $responsable->refresh();
    expect($responsable->telefono_e164)->toBe('+5492995123456')->and($responsable->telefono_original)->toBe('0299 15 5123456')
        ->and($responsable->telefono_laboral_e164)->toBe('+542994515123')->and($responsable->telefono_laboral_original)->toBe('0299 4515123');
    $this->get(route('academico.responsables.edit', $responsable))->assertInertia(fn (Assert $page) => $page
        ->where('responsable.telefono_es_fijo', false)->where('responsable.telefono_laboral_es_fijo', true));
    $this->patch(route('academico.responsables.update', $responsable), ['responsable' => [
        'apellido' => 'Pérez', 'nombre' => 'Ana', 'telefono' => $responsable->telefono_original,
        'telefono_laboral' => $responsable->telefono_laboral_original,
    ]])->assertSessionHasNoErrors();
    expect($responsable->fresh()->telefono_laboral_e164)->toBe('+542994515123');
});

it('rechaza datos telefónicos inválidos también en responsables', function (mixed $telefono, bool $fijo) {
    $responsable = Responsable::query()->create(['apellido' => 'Pérez', 'nombre' => 'Ana']);
    $this->actingAs(User::factory()->create())->patch(route('academico.responsables.update', $responsable), ['responsable' => [
        'apellido' => 'Pérez', 'nombre' => 'Ana', 'telefono' => $telefono, 'telefono_es_fijo' => $fijo,
    ]])->assertSessionHasErrors('responsable.telefono');
    expect($responsable->fresh()->telefono_e164)->toBeNull();
})->with([
    'array' => [['2995123456'], false], 'móvil como fijo' => ['0299 15 5123456', true], 'corto' => ['299', false],
]);

it('permite teléfonos nacionales al crear un responsable desde alumnos', function () {
    $this->seed(TiposVinculoSeeder::class);
    $alumno = Alumno::query()->create(['legajo' => 'TEL-AL', 'apellido' => 'Pérez', 'nombre' => 'Ana']);
    $this->actingAs(User::factory()->create())->post(route('academico.alumnos.responsables.store', $alumno), [
        'responsable' => ['apellido' => 'Pérez', 'nombre' => 'María', 'telefono' => '2994167362'],
        'vinculo' => [
            'vinculo_id' => TipoVinculo::query()->firstOrFail()->id,
            'es_responsable_legal' => true, 'es_responsable_pedagogico' => false, 'es_responsable_economico' => false,
            'recibe_comunicaciones' => true, 'recibe_informes_academicos' => false, 'recibe_facturacion' => false,
            'es_contacto_principal' => true, 'es_contacto_emergencia' => false, 'convive_con_alumno' => true,
            'autorizado_retirar' => true, 'habilitado_portal' => false,
        ],
    ])->assertSessionHasNoErrors();
    expect(Responsable::query()->sole()->telefono_e164)->toBe('+5492994167362');
    $this->get(route('academico.alumnos.show', $alumno))->assertInertia(fn (Assert $page) => $page
        ->where('alumno.responsables.0.telefono', '+5492994167362'));
});
