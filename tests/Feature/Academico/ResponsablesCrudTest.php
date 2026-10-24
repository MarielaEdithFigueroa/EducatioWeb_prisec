<?php

use App\Models\Alumno;
use App\Models\AlumnoResponsable;
use App\Models\Ciudad;
use App\Models\Log;
use App\Models\Responsable;
use App\Models\TipoDocumento;
use App\Models\TipoVinculo;
use App\Models\User;
use Database\Seeders\ProvinciasYCiudadesSeeder;
use Database\Seeders\TiposDocumentoSeeder;
use Database\Seeders\TiposVinculoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

function seedCatalogosResponsables(): void
{
    test()->seed([
        ProvinciasYCiudadesSeeder::class,
        TiposDocumentoSeeder::class,
        TiposVinculoSeeder::class,
    ]);
}

/** @return array<string, mixed> */
function datosVinculoResponsable(TipoVinculo $tipoVinculo): array
{
    return [
        'vinculo_id' => $tipoVinculo->id,
        'fecha_desde' => '2026-09-02',
        'fecha_hasta' => null,
        'es_responsable_legal' => true,
        'es_responsable_pedagogico' => false,
        'es_responsable_economico' => true,
        'recibe_comunicaciones' => true,
        'recibe_informes_academicos' => false,
        'recibe_facturacion' => true,
        'es_contacto_principal' => true,
        'es_contacto_emergencia' => false,
        'convive_con_alumno' => true,
        'autorizado_retirar' => true,
        'habilitado_portal' => false,
        'observaciones' => 'Contacto preferente',
    ];
}

it('protege el flujo de responsables', function () {
    $this->get(route('academico.responsables.index'))
        ->assertRedirect(route('login'));

    $responsable = Responsable::query()->create(['apellido' => 'Pérez', 'nombre' => 'María']);
    $this->get(route('academico.responsables.show', $responsable))->assertRedirect(route('login'));
    $this->get(route('academico.responsables.edit', $responsable))->assertRedirect(route('login'));
    $this->patch(route('academico.responsables.update', $responsable))->assertRedirect(route('login'));

    $alumno = Alumno::query()->create([
        'legajo' => 'RESP-001',
        'apellido' => 'Alumno',
        'nombre' => 'Protegido',
    ]);

    $this->get(route('academico.alumnos.responsables.buscar', [
        'alumno' => $alumno,
        'buscar' => 'Pérez',
    ]))->assertRedirect(route('login'));

    $this->get(route('academico.alumnos.responsables.create', $alumno))
        ->assertRedirect(route('login'));

    $this->post(route('academico.alumnos.responsables.vincular', $alumno))
        ->assertRedirect(route('login'));
});

it('lista responsables con sus contactos y alumnos activos e históricos', function () {
    $this->seed([TiposDocumentoSeeder::class, TiposVinculoSeeder::class]);
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $madre = TipoVinculo::query()->where('nombre', 'Madre')->sole();
    $alumno = Alumno::query()->create([
        'activo' => false,
        'legajo' => 'RESP-LISTADO',
        'apellido' => 'Pérez',
        'nombre' => 'Ana',
    ]);
    $responsable = Responsable::query()->create([
        'apellido' => 'Pérez',
        'nombre' => 'María',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '25111222',
        'email' => 'maria@example.com',
        'telefono_e164' => '+5492994123456',
        'observaciones' => 'Dato que no necesita el listado',
    ]);
    $inactivo = Responsable::query()->create([
        'activo' => false,
        'apellido' => 'Acuña',
        'nombre' => 'Carlos',
    ]);
    AlumnoResponsable::query()->create([
        'activo' => false,
        'alumno_id' => $alumno->id,
        'responsable_id' => $responsable->id,
        'vinculo_id' => $madre->id,
    ]);

    $this->actingAs($usuario)
        ->get(route('academico.responsables.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/responsables/index')
            ->has('responsables', 2)
            ->where('responsables.0.id', $responsable->id)
            ->where('responsables.0.tipo_documento.nombre', 'DNI')
            ->where('responsables.0.email', 'maria@example.com')
            ->where('responsables.0.telefono_e164', '+5492994123456')
            ->missing('responsables.0.observaciones')
            ->has('responsables.0.alumnos_responsables', 1)
            ->where('responsables.0.alumnos_responsables.0.activo', false)
            ->where('responsables.0.alumnos_responsables.0.alumno.id', $alumno->id)
            ->where('responsables.0.alumnos_responsables.0.alumno.activo', false)
            ->where('responsables.1.id', $inactivo->id)
            ->where('responsables.1.activo', false)
            ->where('responsables.1.tipo_documento', null)
            ->has('responsables.1.alumnos_responsables', 0)
        );
});

it('muestra el listado de responsables vacío', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('academico.responsables.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/responsables/index')
            ->has('responsables', 0)
        );
});

it('filtra responsables por texto literal, estado, identificadores y alumnos vinculados', function () {
    $this->seed(TiposVinculoSeeder::class);
    $usuario = User::factory()->create();
    $responsable = Responsable::query()->create([
        'activo' => false,
        'apellido' => 'Pérez_100%',
        'nombre' => 'María',
        'cuilt' => '20123456786',
        'telefono_e164' => '+5492994123456',
    ]);
    Responsable::query()->create(['apellido' => 'PérezX100ABC', 'nombre' => 'Carlos']);
    $alumno = Alumno::query()->create(['legajo' => 'FILTRO-RESP', 'apellido' => 'Alumno', 'nombre' => 'Ana']);
    AlumnoResponsable::query()->create([
        'activo' => false,
        'alumno_id' => $alumno->id,
        'responsable_id' => $responsable->id,
        'vinculo_id' => TipoVinculo::query()->firstOrFail()->id,
    ]);

    foreach (['Pérez_100%', '20-12345678-6', '+54 9 299 412-3456', 'FILTRO-RESP'] as $buscar) {
        $this->actingAs($usuario)
            ->get(route('academico.responsables.index', ['buscar' => $buscar]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('academico/responsables/index')
                ->where('filters.buscar', $buscar)
                ->where('filters.solo_activos', false)
                ->where('resultados.total', 2)
                ->where('resultados.filtrados', 1)
                ->has('responsables', 1)
                ->where('responsables.0.id', $responsable->id)
            );
    }

    $this->get(route('academico.responsables.index', ['buscar' => 'Pérez_100%', 'solo_activos' => 1]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('responsables', 0)
            ->where('filters.solo_activos', true)
            ->where('resultados.filtrados', 0)
        );

    $this->get(route('academico.responsables.index', ['solo_activos' => 'desconocido', 'buscar' => str_repeat('x', 101)]))
        ->assertSessionHasErrors(['solo_activos', 'buscar']);
});

it('filtra responsables por domicilio y conserva opciones históricas y combinaciones vacías', function () {
    $this->seed(ProvinciasYCiudadesSeeder::class);
    $ciudad = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $otraCiudad = Ciudad::query()->where('provincia_id', '!=', $ciudad->provincia_id)->firstOrFail();
    $ciudad->update(['activo' => false]);
    $ciudad->provincia->update(['activo' => false]);
    $responsable = Responsable::query()->create([
        'apellido' => 'Pérez', 'nombre' => 'Ana', 'ciudad_id' => $ciudad->id,
    ]);
    Responsable::query()->create([
        'activo' => false, 'apellido' => 'Pérez', 'nombre' => 'Juan', 'ciudad_id' => $ciudad->id,
    ]);
    Responsable::query()->create([
        'apellido' => 'Otra', 'nombre' => 'Persona', 'ciudad_id' => $otraCiudad->id,
        'ciudad_laboral_id' => $ciudad->id,
    ]);
    Responsable::query()->create(['apellido' => 'Sin', 'nombre' => 'Domicilio']);
    $this->actingAs(User::factory()->create());

    foreach (['provincia_id' => $ciudad->provincia_id, 'ciudad_id' => $ciudad->id] as $campo => $valor) {
        $this->get(route('academico.responsables.index', [$campo => $valor]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where("filters.$campo", (string) $valor)
                ->has('responsables', 2)
                ->where('resultados.total', 4)
                ->where('resultados.filtrados', 2)
            );
    }

    $this->get(route('academico.responsables.index', [
        'provincia_id' => $ciudad->provincia_id, 'ciudad_id' => $ciudad->id, 'solo_activos' => 1,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('responsables', 1)
            ->where('responsables.0.id', $responsable->id)
            ->where('resultados.filtrados', 1)
            ->where('ciudades', fn ($opciones) => collect($opciones)->contains(
                fn ($opcion) => $opcion['value'] === $ciudad->id && str_ends_with($opcion['label'], ' (inactiva)'),
            ))
            ->where('provincias', fn ($opciones) => collect($opciones)->contains(
                fn ($opcion) => $opcion['value'] === $ciudad->provincia_id && str_ends_with($opcion['label'], ' (inactiva)'),
            ))
        );

    $this->get(route('academico.responsables.index', [
        'provincia_id' => $ciudad->provincia_id, 'ciudad_id' => $otraCiudad->id,
    ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('responsables', 0)
            ->where('resultados.filtrados', 0)
            ->where('filters.provincia_id', (string) $ciudad->provincia_id)
            ->where('filters.ciudad_id', (string) $otraCiudad->id)
        );

    $this->get(route('academico.responsables.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('responsables', 4)
            ->where('resultados.total', 4)
            ->where('resultados.filtrados', 4)
            ->where('filters', ['buscar' => '', 'provincia_id' => '', 'ciudad_id' => '', 'solo_activos' => false])
        );

    $this->get(route('academico.responsables.index', ['provincia_id' => -1, 'ciudad_id' => -1]))
        ->assertSessionHasErrors(['provincia_id', 'ciudad_id']);
});

it('muestra la ficha y prepara la edición conservando localidades históricas', function () {
    seedCatalogosResponsables();
    $ciudad = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $ciudad->update(['activo' => false]);
    $responsable = Responsable::query()->create([
        'apellido' => 'Pérez',
        'nombre' => 'María',
        'fecha_nacimiento' => '1980-02-03',
        'ciudad_id' => $ciudad->id,
        'ciudad_laboral_id' => $ciudad->id,
        'telefono_e164' => '+5492994123456',
        'telefono_original' => '+54 9 299 412-3456',
        'observaciones' => "Primera línea\nSegunda línea",
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('academico.responsables.show', $responsable))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/responsables/show')
            ->where('responsable.id', $responsable->id)
            ->where('responsable.fecha_nacimiento', '1980-02-03')
            ->where('responsable.ciudad.activo', false)
            ->where('responsable.observaciones', "Primera línea\nSegunda línea")
        );

    $this->get(route('academico.responsables.edit', $responsable))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/responsables/formulario')
            ->where('alumno', null)
            ->where('responsable.id', $responsable->id)
            ->where('responsable.telefono', '+54 9 299 412-3456')
            ->where('ciudades', fn ($ciudades) => collect($ciudades)->contains('id', $ciudad->id))
        );
});

it('actualiza los datos personales con auditoría sin modificar vínculos ni estado', function () {
    seedCatalogosResponsables();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $ciudad = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $ciudad->update(['activo' => false]);
    $responsable = Responsable::query()->create([
        'activo' => false,
        'apellido' => 'Pérez',
        'nombre' => 'María',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '12345678',
        'cuilt' => '20123456786',
        'ciudad_id' => $ciudad->id,
        'ciudad_laboral_id' => $ciudad->id,
    ]);
    $alumno = Alumno::query()->create(['legajo' => 'EDIT-RESP', 'apellido' => 'Alumno', 'nombre' => 'Ana']);
    $vinculo = AlumnoResponsable::query()->create([
        'alumno_id' => $alumno->id,
        'responsable_id' => $responsable->id,
        'vinculo_id' => TipoVinculo::query()->firstOrFail()->id,
        'observaciones' => 'Conservar vínculo',
    ]);
    $datosVinculo = $vinculo->refresh()->attributesToArray();
    $datos = [
        'apellido' => '  Pérez  ',
        'nombre' => ' María   Elena ',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '12345678',
        'cuilt' => '20-12345678-6',
        'ciudad_id' => $ciudad->id,
        'ciudad_laboral_id' => $ciudad->id,
        'email' => ' MARIA@EXAMPLE.COM ',
        'telefono' => '+54 9 299 412-3456',
        'observaciones' => "Primera línea\nSegunda línea",
        'activo' => true,
    ];

    $this->actingAs(User::factory()->create())
        ->patch(route('academico.responsables.update', $responsable), [
            'responsable' => $datos,
            'vinculo' => ['observaciones' => 'No aplicar'],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('academico.responsables.show', $responsable));

    expect($responsable->refresh()->nombre)->toBe('María Elena')
        ->and($responsable->email)->toBe('maria@example.com')
        ->and($responsable->telefono_e164)->toBe('+5492994123456')
        ->and($responsable->activo)->toBeFalse()
        ->and($vinculo->refresh()->attributesToArray())->toBe($datosVinculo);

    $log = Log::query()->where('entidad', 'responsables')->where('entidad_id', $responsable->id)->sole();
    expect($log->accion)->toBe('update')
        ->and($log->anterior['nombre'])->toBe('María')
        ->and($log->nuevo['nombre'])->toBe('María Elena');

    $this->patch(route('academico.responsables.update', $responsable), ['responsable' => $datos])
        ->assertSessionHasNoErrors();
    expect(Log::query()->where('entidad', 'responsables')->count())->toBe(1);
});

it('rechaza duplicados, teléfonos inválidos y nuevas localidades inactivas al editar', function () {
    seedCatalogosResponsables();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $ciudad = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $ciudad->update(['activo' => false]);
    Responsable::query()->create([
        'apellido' => 'Existente', 'nombre' => 'María',
        'tipo_documento_id' => $dni->id, 'numero_documento' => '12345678', 'cuilt' => '20123456786',
    ]);
    $responsable = Responsable::query()->create(['apellido' => 'Pérez', 'nombre' => 'Juan']);

    $this->actingAs(User::factory()->create())
        ->patch(route('academico.responsables.update', $responsable), ['responsable' => [
            'apellido' => '', 'nombre' => 'Juan',
            'tipo_documento_id' => $dni->id, 'numero_documento' => '12345678', 'cuilt' => '20-12345678-6',
            'telefono' => '299412', 'ciudad_id' => $ciudad->id, 'ciudad_laboral_id' => $ciudad->id,
        ]])
        ->assertSessionHasErrors([
            'responsable.apellido', 'responsable.numero_documento', 'responsable.cuilt',
            'responsable.telefono', 'responsable.ciudad_id', 'responsable.ciudad_laboral_id',
        ]);

    expect($responsable->refresh()->apellido)->toBe('Pérez')
        ->and(Log::query()->where('entidad', 'responsables')->count())->toBe(0);
});

it('revierte la edición si falla el registro de auditoría', function () {
    $responsable = Responsable::query()->create(['apellido' => 'Pérez', 'nombre' => 'María']);
    $dispatcher = Log::getEventDispatcher();
    Log::setEventDispatcher(clone $dispatcher);
    Log::creating(function (): void {
        throw new RuntimeException('Falla de auditoría');
    });

    try {
        $this->actingAs(User::factory()->create());
        $this->withoutExceptionHandling();
        expect(fn () => $this->patch(route('academico.responsables.update', $responsable), [
            'responsable' => ['apellido' => 'Otro', 'nombre' => 'Nombre'],
        ]))->toThrow(RuntimeException::class, 'Falla de auditoría');
        expect($responsable->refresh()->apellido)->toBe('Pérez');
    } finally {
        Log::setEventDispatcher($dispatcher);
    }
});

it('busca responsables existentes e informa vínculos activos e históricos', function () {
    seedCatalogosResponsables();
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $madre = TipoVinculo::query()->where('nombre', 'Madre')->sole();
    $alumno = Alumno::query()->create([
        'legajo' => 'RESP-002',
        'apellido' => 'Pérez',
        'nombre' => 'Ana',
    ]);
    $otroAlumno = Alumno::query()->create([
        'legajo' => 'RESP-003',
        'apellido' => 'Pérez',
        'nombre' => 'Juan',
    ]);
    $responsable = Responsable::query()->create([
        'activo' => false,
        'apellido' => 'Pérez_100%',
        'nombre' => 'María',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '25111222',
        'cuilt' => '27251112229',
        'email' => 'maria@example.com',
        'telefono_e164' => '+5492994123456',
    ]);
    Responsable::query()->create([
        'apellido' => 'PérezX100ABC',
        'nombre' => 'No debe aparecer',
    ]);

    AlumnoResponsable::query()->create([
        ...datosVinculoResponsable($madre),
        'activo' => false,
        'alumno_id' => $alumno->id,
        'responsable_id' => $responsable->id,
        'fecha_hasta' => '2026-08-01',
    ]);
    AlumnoResponsable::query()->create([
        ...datosVinculoResponsable($madre),
        'alumno_id' => $otroAlumno->id,
        'responsable_id' => $responsable->id,
    ]);

    $this->actingAs($usuario)
        ->getJson(route('academico.alumnos.responsables.buscar', [
            'alumno' => $alumno,
            'buscar' => 'Pérez_100%',
        ]))
        ->assertOk()
        ->assertJsonCount(1, 'responsables')
        ->assertJsonPath('responsables.0.id', $responsable->id)
        ->assertJsonPath('responsables.0.activo', false)
        ->assertJsonPath('responsables.0.vinculo.activo', false)
        ->assertJsonPath('responsables.0.vinculo.es_responsable_economico', true)
        ->assertJsonPath('responsables.0.otros_alumnos.0.id', $otroAlumno->id);

    $this->actingAs($usuario)
        ->getJson(route('academico.alumnos.responsables.buscar', [
            'alumno' => $alumno,
            'buscar' => '2994123456',
        ]))
        ->assertOk()
        ->assertJsonPath('responsables.0.id', $responsable->id);
});

it('vincula, actualiza y reactiva la misma fila con auditoría', function () {
    seedCatalogosResponsables();
    $usuario = User::factory()->create();
    $madre = TipoVinculo::query()->where('nombre', 'Madre')->sole();
    $padre = TipoVinculo::query()->where('nombre', 'Padre')->sole();
    $alumno = Alumno::query()->create([
        'legajo' => 'RESP-004',
        'apellido' => 'Acuña',
        'nombre' => 'Sofía',
    ]);
    $responsable = Responsable::query()->create([
        'activo' => false,
        'apellido' => 'Acuña',
        'nombre' => 'Carlos',
    ]);

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.responsables.vincular', $alumno), [
            'responsable_id' => $responsable->id,
            'vinculo' => datosVinculoResponsable($padre),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('academico.alumnos.show', [
            'alumno' => $alumno,
            'seccion' => 'responsables',
        ]));

    $vinculo = AlumnoResponsable::query()->sole();
    expect($responsable->refresh()->activo)->toBeTrue()
        ->and($vinculo->activo)->toBeTrue()
        ->and($vinculo->es_responsable_economico)->toBeTrue()
        ->and(Log::query()->where('entidad', 'responsables')->where('accion', 'reactivate')->exists())
        ->toBeTrue()
        ->and(Log::query()->where('entidad', 'alumnos_responsables')->where('accion', 'create')->exists())
        ->toBeTrue();

    $vinculo->fill(['activo' => false, 'fecha_hasta' => '2026-08-20'])->save();
    $datosActualizados = [
        ...datosVinculoResponsable($madre),
        'fecha_desde' => '2026-09-03',
        'es_responsable_economico' => false,
    ];

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.responsables.vincular', $alumno), [
            'responsable_id' => $responsable->id,
            'vinculo' => $datosActualizados,
        ])
        ->assertSessionHasNoErrors();

    expect(AlumnoResponsable::query()->count())->toBe(1)
        ->and($vinculo->refresh()->activo)->toBeTrue()
        ->and($vinculo->vinculo_id)->toBe($madre->id)
        ->and($vinculo->fecha_hasta)->toBeNull()
        ->and($vinculo->es_responsable_economico)->toBeFalse()
        ->and(Log::query()
            ->where('entidad', 'alumnos_responsables')
            ->where('entidad_id', $vinculo->id)
            ->where('accion', 'reactivate')
            ->exists())
        ->toBeTrue();
});

it('muestra el alta y crea responsable más vínculo en una transacción', function () {
    seedCatalogosResponsables();
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $neuquen = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $madre = TipoVinculo::query()->where('nombre', 'Madre')->sole();
    $alumno = Alumno::query()->create([
        'legajo' => 'RESP-005',
        'apellido' => 'Acuña',
        'nombre' => 'Sofía',
    ]);

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.responsables.create', $alumno))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/responsables/formulario')
            ->where('alumno.id', $alumno->id)
            ->has('tiposDocumento')
            ->has('ciudades')
            ->has('tiposVinculo')
        );

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.responsables.store', $alumno), [
            'responsable' => [
                'apellido' => '  Acuña ',
                'nombre' => ' María   Elena ',
                'tipo_documento_id' => $dni->id,
                'numero_documento' => ' ab 123 ',
                'cuilt' => '20-12345678-6',
                'email' => ' MARIA@EXAMPLE.COM ',
                'telefono' => '+54 9 299 412-3456',
                'domicilio' => ' Calle  123 ',
                'ciudad_id' => $neuquen->id,
                'cpa' => ' q8300xyz ',
                'profesion' => ' Docente ',
                'telefono_laboral' => '+54 299 455-6677',
            ],
            'vinculo' => datosVinculoResponsable($madre),
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('academico.alumnos.show', [
            'alumno' => $alumno,
            'seccion' => 'responsables',
        ]));

    $responsable = Responsable::query()->sole();
    $vinculo = AlumnoResponsable::query()->sole();

    expect($responsable->apellido)->toBe('Acuña')
        ->and($responsable->nombre)->toBe('María Elena')
        ->and($responsable->numero_documento)->toBe('AB 123')
        ->and($responsable->cuilt)->toBe('20123456786')
        ->and($responsable->email)->toBe('maria@example.com')
        ->and($responsable->telefono_e164)->toBe('+5492994123456')
        ->and($responsable->telefono_original)->toBe('+54 9 299 412-3456')
        ->and($responsable->ciudad_id)->toBe($neuquen->id)
        ->and($vinculo->alumno_id)->toBe($alumno->id)
        ->and($vinculo->responsable_id)->toBe($responsable->id)
        ->and(Log::query()->where('entidad', 'responsables')->where('accion', 'create')->exists())
        ->toBeTrue()
        ->and(Log::query()->where('entidad', 'alumnos_responsables')->where('accion', 'create')->exists())
        ->toBeTrue();
});

it('valida duplicados y todos los datos obligatorios del vínculo', function () {
    seedCatalogosResponsables();
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $alumno = Alumno::query()->create([
        'legajo' => 'RESP-006',
        'apellido' => 'Alumno',
        'nombre' => 'Validación',
    ]);
    Responsable::query()->create([
        'apellido' => 'Existente',
        'nombre' => 'Responsable',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '12345678',
        'cuilt' => '20123456786',
    ]);

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.responsables.store', $alumno), [
            'responsable' => [
                'apellido' => '',
                'nombre' => '',
                'tipo_documento_id' => $dni->id,
                'numero_documento' => '12345678',
                'cuilt' => '20-12345678-6',
                'telefono' => '299412',
            ],
            'vinculo' => [],
        ])
        ->assertSessionHasErrors([
            'responsable.apellido',
            'responsable.nombre',
            'responsable.numero_documento',
            'responsable.cuilt',
            'responsable.telefono',
            'vinculo.vinculo_id',
            'vinculo.es_responsable_legal',
        ]);

    expect(Responsable::query()->count())->toBe(1)
        ->and(AlumnoResponsable::query()->count())->toBe(0);
});
