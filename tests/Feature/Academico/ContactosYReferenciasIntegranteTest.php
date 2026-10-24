<?php

use App\Models\ContactoIntegrante;
use App\Models\Integrante;
use App\Models\Log;
use App\Models\ReferenciaLaboralIntegrante;
use App\Models\User;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

function datosRelacionesIntegrante(array $extra = []): array
{
    return [...['legajo' => 'REL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana'], ...$extra];
}

it('crea varios contactos y referencias con teléfonos normalizados y auditoría', function () {
    $this->post(route('academico.integrantes.store'), datosRelacionesIntegrante([
        'referencias_laborales' => [
            ['id' => null, 'lugar' => ' Escuela  Norte ', 'cargo' => 'Profesora', 'tiempo' => '3 años', 'referencia_1_nombre' => 'María', 'referencia_1_telefono' => '2995123456', 'referencia_1_telefono_es_fijo' => false, 'referencia_2_nombre' => 'José', 'referencia_2_telefono' => '2994515123', 'referencia_2_telefono_es_fijo' => true, 'observaciones' => " Una línea\nOtra "],
            ['id' => null, 'lugar' => 'Escuela Sur'],
        ],
        'contactos' => [
            ['id' => null, 'nombre' => ' Juan  Pérez ', 'telefono' => '2995123456', 'telefono_es_fijo' => false, 'vinculo' => 'Cónyuge', 'observaciones' => 'Contacto principal'],
            ['id' => null, 'nombre' => 'Laura', 'telefono' => '2994515123', 'telefono_es_fijo' => true, 'vinculo' => 'Amistad'],
        ],
    ]))->assertSessionHasNoErrors();
    $integrante = Integrante::query()->sole();
    expect($integrante->referenciasLaborales)->toHaveCount(2)->and($integrante->contactos)->toHaveCount(2);
    $referencia = $integrante->referenciasLaborales->first();
    expect($referencia->lugar)->toBe('Escuela Norte')->and($referencia->referencia_1_telefono)->toBe('+5492995123456')
        ->and($referencia->referencia_2_telefono)->toBe('+542994515123')->and($referencia->observaciones)->toBe("Una línea\nOtra")
        ->and($integrante->contactos->first()->nombre)->toBe('Juan Pérez');
    expect(Log::query()->whereIn('entidad', ['integrantes_contactos', 'integrantes_referencias_laborales'])->count())->toBe(4);
    foreach (['show', 'edit', 'imprimir'] as $pantalla) {
        $this->get(route('academico.integrantes.'.$pantalla, $integrante))->assertInertia(fn (Assert $page) => $page
            ->has('integrante.contactos', 2)->has('integrante.referencias_laborales', 2)
            ->where('integrante.contactos.0.vinculo', 'Cónyuge')
            ->where('integrante.contactos.1.telefono_es_fijo', true)
            ->where('integrante.referencias_laborales.0.referencia_2_telefono_es_fijo', true)
            ->missing('integrante.conyuge_nombre')->missing('integrante.referencia_lugar'));
    }
    $this->get(route('academico.integrantes.create'))->assertInertia(fn (Assert $page) => $page
        ->where('catalogos.vinculos_contacto', ContactoIntegrante::VINCULOS));
});

it('modifica conserva teléfonos fijos y da de baja sin borrar filas', function () {
    $integrante = Integrante::factory()->create(['legajo' => 'REL-1']);
    $referencia = ReferenciaLaboralIntegrante::factory()->create(['integrante_id' => $integrante->id, 'referencia_1_telefono' => '+542994515123']);
    $contacto = ContactoIntegrante::factory()->create(['integrante_id' => $integrante->id, 'telefono' => '+542995123456']);
    $datos = datosRelacionesIntegrante([
        'referencias_laborales' => [['id' => $referencia->id, 'lugar' => 'Otro colegio', 'referencia_1_telefono' => '2994515123']],
        'contactos' => [['id' => $contacto->id, 'nombre' => 'Laura', 'telefono' => '2995123456']],
    ]);
    $this->patch(route('academico.integrantes.update', $integrante), $datos)->assertSessionHasNoErrors();
    expect($referencia->fresh()->referencia_1_telefono)->toBe('+542994515123')->and($contacto->fresh()->telefono)->toBe('+542995123456');
    $logs = Log::query()->count();
    $this->patch(route('academico.integrantes.update', $integrante), $datos)->assertSessionHasNoErrors();
    expect(Log::query()->count())->toBe($logs);
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante([
        'contactos_bajas' => [$contacto->id], 'referencias_laborales_bajas' => [$referencia->id],
    ]))->assertSessionHasNoErrors();
    expect($contacto->fresh()->activo)->toBeFalse()->and($referencia->fresh()->activo)->toBeFalse()
        ->and(ContactoIntegrante::query()->count())->toBe(1)->and(ReferenciaLaboralIntegrante::query()->count())->toBe(1);
    $this->get(route('academico.integrantes.show', $integrante))->assertInertia(fn (Assert $page) => $page
        ->has('integrante.contactos', 0)->has('integrante.referencias_laborales', 0));
    expect(Log::query()->where('accion', 'deactivate')->count())->toBe(2);
});

it('no quita filas omitidas ni filas agregadas desde otra sesión', function () {
    $integrante = Integrante::factory()->create(['legajo' => 'REL-1']);
    $referencia = ReferenciaLaboralIntegrante::factory()->create(['integrante_id' => $integrante->id]);
    $contacto = ContactoIntegrante::factory()->create(['integrante_id' => $integrante->id]);
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante())->assertSessionHasNoErrors();
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante(['contactos' => [], 'referencias_laborales' => []]))->assertSessionHasNoErrors();
    expect($contacto->fresh()->activo)->toBeTrue()->and($referencia->fresh()->activo)->toBeTrue();
});

it('rechaza IDs ajenos en edición y bajas y también al crear un integrante', function (string $coleccion, string $modelo, string $nombre) {
    $ajeno = $modelo::factory()->create();
    $integrante = Integrante::factory()->create(['legajo' => 'REL-1']);
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante([$coleccion => [['id' => $ajeno->id, $nombre => 'Cambio']]]))->assertSessionHasErrors("$coleccion.0.id");
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante([$coleccion.'_bajas' => [$ajeno->id]]))->assertSessionHasErrors($coleccion.'_bajas.0');
    $this->post(route('academico.integrantes.store'), datosRelacionesIntegrante(['legajo' => 'OTRO', $coleccion => [['id' => $ajeno->id, $nombre => 'Cambio']]]))->assertSessionHasErrors("$coleccion.0.id");
    expect($ajeno->fresh()->activo)->toBeTrue()->and($ajeno->fresh()->{$nombre})->toBe($ajeno->{$nombre});
})->with([
    ['contactos', ContactoIntegrante::class, 'nombre'], ['referencias_laborales', ReferenciaLaboralIntegrante::class, 'lugar'],
]);

it('valida datos anidados sin escrituras parciales', function (array $datos, string $campo) {
    $this->post(route('academico.integrantes.store'), datosRelacionesIntegrante($datos))->assertSessionHasErrors($campo);
    expect(Integrante::query()->count())->toBe(0)->and(ContactoIntegrante::query()->count())->toBe(0)->and(ReferenciaLaboralIntegrante::query()->count())->toBe(0);
})->with([
    [['contactos' => [['nombre' => ' ']]], 'contactos.0.nombre'],
    [['contactos' => [['nombre' => 'Juan', 'vinculo' => 'Pareja']]], 'contactos.0.vinculo'],
    [['contactos' => [['nombre' => 'Juan', 'telefono' => 'no']]], 'contactos.0.telefono'],
    [['contactos' => [['nombre' => 'Juan', 'telefono' => ['2995123456']]]], 'contactos.0.telefono'],
    [['contactos' => [['nombre' => 'Juan', 'telefono' => '+5492995123456', 'telefono_es_fijo' => true]]], 'contactos.0.telefono'],
    [['contactos' => [['nombre' => 'Juan', 'telefono_es_fijo' => 'quizás']]], 'contactos.0.telefono_es_fijo'],
    [['contactos' => [['nombre' => 'Juan', 'activo' => false]]], 'contactos.0'],
    [['contactos' => ['texto']], 'contactos.0'],
    [['referencias_laborales' => [['lugar' => '']]], 'referencias_laborales.0.lugar'],
    [['referencias_laborales' => [['lugar' => 'Escuela', 'referencia_2_telefono' => '299']]], 'referencias_laborales.0.referencia_2_telefono'],
    [['referencias_laborales' => [['lugar' => 'Escuela', 'integrante_id' => 1]]], 'referencias_laborales.0'],
]);

it('rechaza IDs repetidos y la edición simultánea con baja de una fila', function () {
    $contacto = ContactoIntegrante::factory()->create();
    $integrante = $contacto->integrante;
    $datos = datosRelacionesIntegrante(['legajo' => $integrante->legajo, 'contactos' => [
        ['id' => $contacto->id, 'nombre' => 'Uno'], ['id' => $contacto->id, 'nombre' => 'Dos'],
    ]]);
    $this->patch(route('academico.integrantes.update', $integrante), $datos)->assertSessionHasErrors('contactos.0.id');
    $datos['contactos'] = [['id' => $contacto->id, 'nombre' => 'Cambio']];
    $datos['contactos_bajas'] = [$contacto->id];
    $this->patch(route('academico.integrantes.update', $integrante), $datos)->assertSessionHasErrors('contactos.0.id');
    expect($contacto->fresh()->nombre)->toBe($contacto->nombre)->and($contacto->fresh()->activo)->toBeTrue();
});

it('impide modificar las tablas de un integrante inactivo', function () {
    $integrante = Integrante::factory()->create(['activo' => false]);
    $this->patch(route('academico.integrantes.update', $integrante), datosRelacionesIntegrante([
        'legajo' => $integrante->legajo, 'contactos' => [['nombre' => 'Juan']],
    ]))->assertSessionHasErrors('legajo');
    expect(ContactoIntegrante::query()->count())->toBe(0);
});

it('revierte todo si falla la auditoría de un contacto después de guardar referencias', function () {
    $this->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'integrantes_contactos') {
            throw new RuntimeException('Error de auditoría');
        }
    });
    expect(fn () => $this->post(route('academico.integrantes.store'), datosRelacionesIntegrante([
        'referencias_laborales' => [['lugar' => 'Escuela']], 'contactos' => [['nombre' => 'Juan']],
    ])))->toThrow(RuntimeException::class, 'Error de auditoría');
    expect(Integrante::query()->count())->toBe(0)->and(ReferenciaLaboralIntegrante::query()->count())->toBe(0)
        ->and(ContactoIntegrante::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});
