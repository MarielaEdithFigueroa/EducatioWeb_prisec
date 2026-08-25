<?php

use App\Models\Alumno;
use App\Models\Ciudad;
use App\Models\GrupoSanguineo;
use App\Models\Log;
use App\Models\MotivoBaja;
use App\Models\Nacionalidad;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\GruposSanguineosSeeder;
use Database\Seeders\MotivosBajaSeeder;
use Database\Seeders\NacionalidadesSeeder;
use Database\Seeders\ProvinciasYCiudadesSeeder;
use Database\Seeders\TiposDocumentoSeeder;
use Inertia\Testing\AssertableInertia as Assert;

function seedCatalogosAlumnos(): void
{
    test()->seed([
        ProvinciasYCiudadesSeeder::class,
        NacionalidadesSeeder::class,
        GruposSanguineosSeeder::class,
        MotivosBajaSeeder::class,
        TiposDocumentoSeeder::class,
    ]);
}

it('protege las pantallas de alumnos', function () {
    $this->get(route('academico.alumnos.index'))
        ->assertRedirect(route('login'));

    $this->get(route('academico.alumnos.create'))
        ->assertRedirect(route('login'));
});

it('administra la ficha del alumno con baja lógica y auditoría', function () {
    seedCatalogosAlumnos();
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();
    $neuquen = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $nacionalidad = Nacionalidad::query()->where('codigo', 'ARG')->sole();
    $grupoSanguineo = GrupoSanguineo::query()->where('codigo', 'O+')->sole();

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/alumnos/formulario')
            ->where('alumno', null)
            ->has('tiposDocumento')
            ->has('ciudades')
            ->has('nacionalidades')
            ->has('gruposSanguineos')
        );

    $datos = [
        'legajo' => ' Sec-2026/001 ',
        'apellido' => '  Pérez  ',
        'nombre' => '  Ana   María ',
        'nombre_elegido' => ' Ani ',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => ' ab 123 ',
        'cuilt' => '20-12345678-6',
        'fecha_nacimiento' => '2012-05-20',
        'ciudad_nacimiento_id' => $neuquen->id,
        'nacionalidad_id' => $nacionalidad->id,
        'grupo_sanguineo_id' => $grupoSanguineo->id,
        'sexo_registral' => 'f',
        'genero' => 'Mujer',
        'genero_autodescripcion' => 'No debe persistir',
        'email' => ' ANA@EXAMPLE.COM ',
        'domicilio' => ' Calle  123 ',
        'ciudad_id' => $neuquen->id,
        'cpa' => ' q8300xyz ',
        'fecha_ingreso' => '2026-02-10',
        'fecha_inicio_cursado' => '2026-02-20',
        'libro' => ' 12 ',
        'folio' => ' 34 ',
        'autoriza_uso_imagen' => true,
        'observaciones' => "  Primera línea\nSegunda línea  ",
    ];

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.store'), $datos)
        ->assertSessionHasNoErrors();

    $alumno = Alumno::query()->where('legajo', 'Sec-2026/001')->sole();

    expect($alumno->activo)->toBeTrue()
        ->and($alumno->apellido)->toBe('Pérez')
        ->and($alumno->nombre)->toBe('Ana María')
        ->and($alumno->numero_documento)->toBe('AB 123')
        ->and($alumno->cuilt)->toBe('20123456786')
        ->and($alumno->sexo_registral)->toBe('F')
        ->and($alumno->genero_autodescripcion)->toBeNull()
        ->and($alumno->email)->toBe('ana@example.com')
        ->and($alumno->cpa)->toBe('Q8300XYZ')
        ->and($alumno->autoriza_uso_imagen)->toBeTrue();

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.edit', $alumno))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/alumnos/formulario')
            ->where('alumno.id', $alumno->id)
            ->where('alumno.fecha_nacimiento', '2012-05-20')
        );

    expect(Log::query()
        ->where('entidad', 'alumnos')
        ->where('entidad_id', $alumno->id)
        ->where('accion', 'create')
        ->exists())
        ->toBeTrue();

    $datosActualizados = [
        ...$datos,
        'legajo' => 'SEC-2026/001-A',
        'apellido' => 'Pérez Gómez',
        'genero' => 'Otra identidad',
        'genero_autodescripcion' => 'Género fluido',
        'autoriza_uso_imagen' => false,
    ];

    $this->actingAs($usuario)
        ->patch(route('academico.alumnos.update', $alumno), $datosActualizados)
        ->assertSessionHasNoErrors();

    expect($alumno->refresh()->legajo)->toBe('SEC-2026/001-A')
        ->and($alumno->apellido)->toBe('Pérez Gómez')
        ->and($alumno->genero_autodescripcion)->toBe('Género fluido')
        ->and($alumno->autoriza_uso_imagen)->toBeFalse()
        ->and(Log::query()
            ->where('entidad', 'alumnos')
            ->where('entidad_id', $alumno->id)
            ->where('accion', 'update')
            ->exists())
        ->toBeTrue();

    $motivoBaja = MotivoBaja::query()->where('nombre', 'Cambio de establecimiento')->sole();

    $this->actingAs($usuario)
        ->patch(route('academico.alumnos.desactivar', $alumno), [
            'fecha_baja' => '2026-08-24',
            'motivo_baja_id' => $motivoBaja->id,
        ])
        ->assertSessionHasNoErrors();

    expect($alumno->refresh()->activo)->toBeFalse()
        ->and($alumno->fecha_baja?->format('Y-m-d'))->toBe('2026-08-24')
        ->and($alumno->motivo_baja_id)->toBe($motivoBaja->id)
        ->and(Log::query()
            ->where('entidad', 'alumnos')
            ->where('entidad_id', $alumno->id)
            ->where('accion', 'deactivate')
            ->exists())
        ->toBeTrue();

    $this->actingAs($usuario)
        ->patch(route('academico.alumnos.reactivar', $alumno))
        ->assertSessionHasNoErrors();

    expect($alumno->refresh()->activo)->toBeTrue()
        ->and($alumno->fecha_baja)->toBeNull()
        ->and($alumno->motivo_baja_id)->toBeNull()
        ->and(Log::query()
            ->where('entidad', 'alumnos')
            ->where('entidad_id', $alumno->id)
            ->where('accion', 'reactivate')
            ->exists())
        ->toBeTrue();
});

it('valida los datos críticos y sus identificadores únicos', function () {
    seedCatalogosAlumnos();
    $usuario = User::factory()->create();
    $dni = TipoDocumento::query()->where('nombre', 'DNI')->sole();

    Alumno::query()->create([
        'legajo' => 'EXISTENTE',
        'apellido' => 'Existente',
        'nombre' => 'Alumno',
        'tipo_documento_id' => $dni->id,
        'numero_documento' => '12345678',
        'cuilt' => '20123456786',
    ]);

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.store'), [
            'legajo' => 'EXISTENTE',
            'apellido' => '',
            'nombre' => '',
            'tipo_documento_id' => $dni->id,
            'numero_documento' => '12345678',
            'cuilt' => '20-12345678-6',
            'fecha_nacimiento' => 'tomorrow',
            'genero' => 'Otra identidad',
            'genero_autodescripcion' => '',
        ])
        ->assertSessionHasErrors([
            'legajo',
            'apellido',
            'nombre',
            'numero_documento',
            'cuilt',
            'fecha_nacimiento',
            'genero_autodescripcion',
        ]);

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.store'), [
            'legajo' => 'NUEVO',
            'apellido' => 'Sin documento',
            'nombre' => 'Alumno',
            'numero_documento' => 'ABC123',
        ])
        ->assertSessionHasErrors('tipo_documento_id');

    expect(Alumno::query()->count())->toBe(1);
});

it('filtra alumnos desde el servidor y conserva los filtros en la URL', function () {
    seedCatalogosAlumnos();
    $usuario = User::factory()->create();
    $neuquen = Ciudad::query()->where('nombre', 'Neuquén')->sole();
    $caba = Ciudad::query()
        ->where('nombre', 'Ciudad Autónoma de Buenos Aires')
        ->sole();

    Alumno::query()->create([
        'legajo' => 'LIT-001',
        'apellido' => 'Pérez_100%',
        'nombre' => 'Ana',
        'cuilt' => '20123456786',
        'ciudad_id' => $neuquen->id,
    ]);
    Alumno::query()->create([
        'activo' => false,
        'legajo' => 'COM-002',
        'apellido' => 'PérezX100ABC',
        'nombre' => 'Bruno',
        'ciudad_id' => $neuquen->id,
    ]);
    Alumno::query()->create([
        'legajo' => 'CABA-003',
        'apellido' => 'Otra',
        'nombre' => 'Carla',
        'ciudad_id' => $caba->id,
    ]);

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.index', [
            'buscar' => '  Pérez_100%  ',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/alumnos/index')
            ->has('alumnos', 1)
            ->where('alumnos.0.legajo', 'LIT-001')
            ->where('filters.buscar', 'Pérez_100%')
            ->where('resultados.total', 3)
            ->where('resultados.filtrados', 1)
            ->has('provincias')
            ->has('ciudades')
        );

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.index', [
            'buscar' => '20-12345678-6',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('alumnos', 1)
            ->where('alumnos.0.legajo', 'LIT-001')
        );

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.index', [
            'provincia_id' => $neuquen->provincia_id,
            'ciudad_id' => $neuquen->id,
            'solo_activos' => '1',
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('alumnos', 1)
            ->where('alumnos.0.legajo', 'LIT-001')
            ->where('filters.provincia_id', (string) $neuquen->provincia_id)
            ->where('filters.ciudad_id', (string) $neuquen->id)
            ->where('filters.solo_activos', true)
            ->where('resultados.filtrados', 1)
        );

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.index', [
            'provincia_id' => $neuquen->provincia_id,
            'ciudad_id' => $caba->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('alumnos', 0)
            ->where('resultados.filtrados', 0)
        );

    $this->actingAs($usuario)
        ->get(route('academico.alumnos.index', ['provincia_id' => 999]))
        ->assertSessionHasErrors('provincia_id');
});
