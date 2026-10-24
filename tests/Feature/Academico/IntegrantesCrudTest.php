<?php

use App\Models\Ciudad;
use App\Models\FuncionEquipo;
use App\Models\Integrante;
use App\Models\IntegranteDocumentacion;
use App\Models\Log;
use App\Models\Nacionalidad;
use App\Models\TipoDocumentacionIntegrante;
use App\Models\TipoDocumento;
use App\Models\User;
use Database\Seeders\IntegranteSeeder;
use Database\Seeders\NacionalidadesSeeder;
use Database\Seeders\ProvinciasYCiudadesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log as Logger;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('adjuntos');
});

function datosIntegrante(array $extra = []): array
{
    return [...['legajo' => '100', 'apellido' => 'Pérez', 'nombre' => 'Ana', 'fecha_ingreso' => '2020-03-01'], ...$extra];
}

function imagenIntegrante(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
}

function fotoIntegrante(): UploadedFile
{
    return UploadedFile::fake()->createWithContent('foto.png', imagenIntegrante());
}

it('protege todas las pantallas y operaciones del legajo integrante', function () {
    $integrante = Integrante::factory()->create();
    foreach (['index', 'create', 'show', 'edit', 'imprimir', 'foto'] as $accion) {
        $this->get(route('academico.integrantes.'.$accion, in_array($accion, ['index', 'create']) ? [] : $integrante))
            ->assertRedirect(route('login'));
    }
    $this->post(route('academico.integrantes.store'), datosIntegrante())->assertRedirect(route('login'));
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.'.$accion, $integrante), datosIntegrante())->assertRedirect(route('login'));
    }
});

it('crea el legajo completo con datos normalizados checklist y auditoría', function () {
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $pendiente = TipoDocumentacionIntegrante::factory()->create();
    $dni = TipoDocumento::query()->create(['nombre' => 'DNI']);
    $usuario = User::factory()->create();
    $this->actingAs($usuario)->post(route('academico.integrantes.store'), datosIntegrante([
        'apellido' => '  Pérez   López ', 'nombre' => ' Ana  María ',
        'tipo_documento_id' => $dni->id, 'numero_documento' => '48123456',
        'cuilt' => '27-48123456-0', 'email' => ' ANA@EXAMPLE.COM ',
        'telefono' => '+54 (351) 412-3456', 'telefono_es_fijo' => true, 'celular' => '+54 9 351 765-4321',
        'estado_civil' => 'Casado/a', 'fecha_nacimiento' => '1985-02-03', 'sexo_registral' => 'F',
        'domicilio' => 'Mitre 12', 'cpa' => 'x5000abc', 'titulo_habilitante' => 'Profesora',
        'numero_registro' => 'R-120', 'tiene_aportes' => true,
        'fecha_residencia_provincia' => '2001-01-01', 'art' => 'ART', 'obra_social' => 'Obra social',
        'cuenta_bancaria' => 'Cuenta 123',
        'horario' => "Lunes a viernes\n8 a 12",
        'documentacion' => [['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true]],
        'activo' => false, 'foto_ruta' => '../../archivo', 'fecha_baja' => '2020-01-01',
    ]))->assertSessionHasNoErrors();

    $integrante = Integrante::query()->sole();
    expect($integrante->apellido)->toBe('Pérez López')->and($integrante->nombre)->toBe('Ana María')
        ->and($integrante->cuilt)->toBe('27481234560')->and($integrante->email)->toBe('ana@example.com')
        ->and($integrante->telefono)->toBe('+543514123456')->and($integrante->celular)->toBe('+5493517654321')
        ->and($integrante->cpa)->toBe('X5000ABC')->and($integrante->activo)->toBeTrue()
        ->and($integrante->foto_ruta)->toBeNull()->and($integrante->fecha_baja)->toBeNull()
        ->and($integrante->titulo_habilitante)->toBe('Profesora')->and($integrante->numero_registro)->toBe('R-120')
        ->and($integrante->documentacion()->count())->toBe(2);
    expect($integrante->documentacion()->where('tipo_documentacion_integrante_id', $tipo->id)->sole()->presentado)->toBeTrue()
        ->and($integrante->documentacion()->where('tipo_documentacion_integrante_id', $pendiente->id)->sole()->presentado)->toBeFalse();
    $log = Log::query()->where('entidad', 'integrantes')->sole();
    expect($log->accion)->toBe('create')->and($log->usuario_id)->toBe($usuario->id)
        ->and($log->nuevo['titulo_habilitante'])->toBe('Profesora');
});

it('abre el alta sin sugerir legajo y conserva filtros al guardar y continuar', function () {
    Integrante::factory()->create(['legajo' => '505', 'activo' => false]);
    Integrante::factory()->create(['legajo' => 'DOC-600']);
    $filtros = ['buscar' => 'Pérez', 'estado' => '', 'funcion_id' => ''];
    $this->actingAs(User::factory()->create())->get(route('academico.integrantes.create', ['filtros' => $filtros]))
        ->assertInertia(fn (Assert $page) => $page->component('academico/integrantes/formulario')
            ->where('integrante', null)->missing('legajoSugerido')->where('filters.estado', '')->where('filters.buscar', 'Pérez'));
    $this->post(route('academico.integrantes.store'), datosIntegrante(['continuar' => true, 'filtros' => $filtros]))
        ->assertSessionHasNoErrors()->assertRedirect(route('academico.integrantes.edit', [
            'integrante' => Integrante::query()->where('legajo', '100')->sole()->id, 'filtros' => $filtros,
        ]));
});

it('filtra por nombre compuesto estado y función', function () {
    $funcion = FuncionEquipo::factory()->create();
    $integrante = Integrante::factory()->create(['apellido' => 'Pérez', 'nombre' => 'Ana María', 'activo' => false]);
    $integrante->asignacionesFunciones()->create(['funcion_equipo_id' => $funcion->id]);
    Integrante::factory()->create(['apellido' => 'Pérez', 'nombre' => 'Ana']);
    $this->actingAs(User::factory()->create())->get(route('academico.integrantes.index', [
        'buscar' => 'Pérez María', 'estado' => 'inactivos', 'funcion_id' => $funcion->id,
    ]))->assertInertia(fn (Assert $page) => $page->component('academico/integrantes/index')->has('integrantes', 1)
        ->where('integrantes.0.id', $integrante->id)->where('resultados.total', 2));
    $this->get(route('academico.integrantes.index'))->assertInertia(fn (Assert $page) => $page->has('integrantes', 1)->where('integrantes.0.activo', true));
    $this->get(route('academico.integrantes.index', ['estado' => '']))->assertInertia(fn (Assert $page) => $page->has('integrantes', 2));
});

it('busca documentos y cuil con separadores y escapa comodines literalmente', function () {
    $integrante = Integrante::factory()->create(['apellido' => 'Pérez%_!', 'numero_documento' => '48123456', 'cuilt' => '27481234569']);
    Integrante::factory()->create(['apellido' => 'Pérez']);
    $this->actingAs(User::factory()->create());
    foreach (['%_!', '48.123.456', '27-48123456-9', $integrante->legajo] as $texto) {
        $this->get(route('academico.integrantes.index', ['buscar' => $texto]))
            ->assertInertia(fn (Assert $page) => $page->has('integrantes', 1)->where('integrantes.0.id', $integrante->id));
    }
    $this->get(route('academico.integrantes.index', ['buscar' => 'Inexistente']))->assertInertia(fn (Assert $page) => $page->has('integrantes', 0));
});

it('valida campos y referencias en el servidor', function (array $datos, string $campo) {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), datosIntegrante($datos))
        ->assertSessionHasErrors($campo);
    expect(Integrante::query()->count())->toBe(0);
})->with([
    'apellido vacío' => [['apellido' => '  '], 'apellido'],
    'nombre inválido' => [['nombre' => ['Ana']], 'nombre'],
    'legajo largo' => [['legajo' => str_repeat('a', 31)], 'legajo'],
    'email' => [['email' => 'incorrecto'], 'email'],
    'cuil' => [['cuilt' => '11111111111'], 'cuilt'],
    'cuil alfabético' => [['cuilt' => 'a27-48123456-9'], 'cuilt'],
    'teléfono' => [['telefono' => '3511234567'], 'telefono'],
    'celular' => [['celular' => 'no'], 'celular'],
    'postal' => [['cpa' => '123'], 'cpa'],
    'fecha imposible' => [['fecha_nacimiento' => '2020-02-31'], 'fecha_nacimiento'],
    'fecha futura' => [['fecha_nacimiento' => '2999-01-01'], 'fecha_nacimiento'],
    'ingreso anterior al nacimiento' => [['fecha_nacimiento' => '2001-01-01', 'fecha_ingreso' => '1990-01-01'], 'fecha_ingreso'],
    'documento sin tipo' => [['numero_documento' => '12345678'], 'tipo_documento_id'],
    'sexo inválido' => [['sexo_registral' => 'Z'], 'sexo_registral'],
    'estado civil libre' => [['estado_civil' => 'Un texto cualquiera'], 'estado_civil'],
    'checkbox inválido' => [['tiene_aportes' => 'quizás'], 'tiene_aportes'],
]);

it('permite elegir y limpiar el estado civil y rechaza texto libre al editar', function (?string $estado) {
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.store'), datosIntegrante(['estado_civil' => $estado]))
        ->assertSessionHasNoErrors();
    $integrante = Integrante::query()->sole();
    expect($integrante->estado_civil)->toBe($estado);

    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['estado_civil' => 'Texto libre']))
        ->assertSessionHasErrors('estado_civil');
    expect($integrante->fresh()->estado_civil)->toBe($estado);
    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['estado_civil' => 'Viudo/a']))
        ->assertSessionHasNoErrors();
    expect($integrante->fresh()->estado_civil)->toBe('Viudo/a');
    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['estado_civil' => '']))
        ->assertSessionHasNoErrors();
    expect($integrante->fresh()->estado_civil)->toBeNull();
})->with(['Soltero/a', 'Casado/a', 'Divorciado/a', 'Separado/a', 'Viudo/a', 'Unión convivencial', null]);

it('envía la lista fija de estados civiles al alta y la edición', function () {
    $integrante = Integrante::factory()->create(['estado_civil' => 'Casado/a']);
    $this->actingAs(User::factory()->create());
    foreach ([route('academico.integrantes.create'), route('academico.integrantes.edit', $integrante)] as $url) {
        $this->get($url)->assertInertia(fn (Assert $page) => $page
            ->where('catalogos.estados_civiles', Integrante::ESTADOS_CIVILES));
    }
    $this->get(route('academico.integrantes.edit', $integrante))->assertInertia(fn (Assert $page) => $page
        ->where('integrante.estado_civil', 'Casado/a'));
});

it('reserva legajos de integrantes inactivos y permite conservar el propio', function () {
    $inactivo = Integrante::factory()->create(['legajo' => '10', 'activo' => false]);
    $activo = Integrante::factory()->create(['legajo' => '20']);
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.store'), datosIntegrante(['legajo' => $inactivo->legajo]))->assertSessionHasErrors('legajo');
    $this->patch(route('academico.integrantes.update', $activo), datosIntegrante(['legajo' => '10']))->assertSessionHasErrors('legajo');
    $this->patch(route('academico.integrantes.update', $activo), datosIntegrante(['legajo' => '20']))->assertSessionHasNoErrors();
});

it('usa funciones sin exponer sector ni mantener el catálogo de sectores', function () {
    $integrante = Integrante::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->get(route('academico.integrantes.index'))->assertInertia(fn (Assert $page) => $page
        ->missing('sectores')->missing('filters.sector_id'));
    $this->get(route('academico.integrantes.edit', $integrante))->assertInertia(fn (Assert $page) => $page
        ->missing('catalogos.sectores')->missing('integrante.sector_id')->missing('integrante.sector'));
    expect(Schema::hasColumn('integrantes', 'sector_id'))->toBeFalse()
        ->and(Schema::hasTable('sectores'))->toBeFalse()
        ->and(Route::has('academico.sectores.index'))->toBeFalse();
});

it('conserva checklist histórico y no borra presentaciones omitidas', function () {
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $integrante = Integrante::factory()->create();
    $this->actingAs(User::factory()->create())->patch(route('academico.integrantes.update', $integrante), datosIntegrante([
        'legajo' => $integrante->legajo, 'documentacion' => [['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true]],
    ]))->assertSessionHasNoErrors();
    $tipo->update(['activo' => false]);
    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['legajo' => $integrante->legajo]))->assertSessionHasNoErrors();
    expect($integrante->documentacion()->sole()->presentado)->toBeTrue();
    $this->get(route('academico.integrantes.show', $integrante))->assertInertia(fn (Assert $page) => $page->where('documentacion.0.activo', false)->where('documentacion.0.presentado', true));
    $this->post(route('academico.integrantes.store'), datosIntegrante(['documentacion' => [['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true]]]))
        ->assertSessionHasErrors('documentacion.0.tipo_documentacion_integrante_id');
});

it('rechaza ids repetidos inexistentes y estados inválidos del checklist', function () {
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.store'), datosIntegrante(['documentacion' => [
        ['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true],
        ['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => false],
    ]]))->assertSessionHasErrors('documentacion.0.tipo_documentacion_integrante_id');
    $this->post(route('academico.integrantes.store'), datosIntegrante(['documentacion' => [
        ['tipo_documentacion_integrante_id' => 99999, 'presentado' => 'quizás'],
    ]]))->assertSessionHasErrors(['documentacion.0.tipo_documentacion_integrante_id', 'documentacion.0.presentado']);
});

it('da de baja exige fecha y motivo preserva el legajo y permite reincorporar', function () {
    $integrante = Integrante::factory()->create();
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $registro = $integrante->documentacion()->create(['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true]);
    $this->actingAs(User::factory()->create());
    $this->patch(route('academico.integrantes.desactivar', $integrante), [])->assertSessionHasErrors(['fecha_baja', 'motivo_baja']);
    $this->patch(route('academico.integrantes.desactivar', $integrante), ['fecha_baja' => '2019-01-01', 'motivo_baja' => 'Retiro'])->assertSessionHasErrors('fecha_baja');
    $baja = ['fecha_baja' => '2025-03-01', 'motivo_baja' => 'Retiro'];
    $this->patch(route('academico.integrantes.desactivar', $integrante), $baja)->assertSessionHasNoErrors();
    $this->patch(route('academico.integrantes.desactivar', $integrante), $baja)->assertSessionHasNoErrors();
    expect($integrante->fresh()->activo)->toBeFalse()->and($registro->fresh()->presentado)->toBeTrue();
    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['legajo' => $integrante->legajo]))->assertSessionHasErrors('legajo');
    $this->get(route('academico.integrantes.edit', $integrante))->assertRedirect();
    $this->patch(route('academico.integrantes.reactivar', $integrante))->assertSessionHasNoErrors();
    $this->patch(route('academico.integrantes.reactivar', $integrante))->assertSessionHasNoErrors();
    expect($integrante->fresh()->activo)->toBeTrue()->and($integrante->fresh()->fecha_baja)->toBeNull()
        ->and($integrante->fresh()->motivo_baja)->toBeNull()->and($registro->fresh()->presentado)->toBeTrue()
        ->and(Log::query()->where('entidad', 'integrantes')->pluck('accion')->all())->toBe(['deactivate', 'reactivate']);
    expect(Log::query()->where('accion', 'reactivate')->sole()->anterior['motivo_baja'])->toBe('Retiro');
});

it('guarda reemplaza y elimina fotos privadas con el formulario multipart', function () {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), datosIntegrante(['foto' => fotoIntegrante()]))->assertSessionHasNoErrors();
    $integrante = Integrante::query()->sole();
    $original = $integrante->foto_ruta;
    Storage::disk('adjuntos')->assertExists($original);
    $this->get(route('academico.integrantes.foto', $integrante))->assertOk()
        ->assertHeader('Content-Type', 'image/png')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'no-store, private')->assertStreamedContent(imagenIntegrante());
    $this->get(route('academico.integrantes.show', $integrante))->assertInertia(fn (Assert $page) => $page->missing('integrante.foto_ruta')->where('integrante.tiene_foto', true));
    $this->post(route('academico.integrantes.update', $integrante), datosIntegrante(['_method' => 'patch', 'foto' => fotoIntegrante()]))->assertSessionHasNoErrors();
    $reemplazo = $integrante->fresh()->foto_ruta;
    expect($reemplazo)->not->toBe($original);
    Storage::disk('adjuntos')->assertMissing($original);
    Storage::disk('adjuntos')->assertExists($reemplazo);
    $this->patch(route('academico.integrantes.update', $integrante), datosIntegrante(['eliminar_foto' => true]))->assertSessionHasNoErrors();
    expect($integrante->fresh()->foto_ruta)->toBeNull();
    Storage::disk('adjuntos')->assertMissing($reemplazo);
    $this->get(route('academico.integrantes.foto', $integrante))->assertNotFound();
});

it('rechaza fotos rectangulares enviadas sin recortar', function () {
    $foto = UploadedFile::fake()->createWithContent('rectangular.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAIAAAABCAIAAAB7QOjdAAAADUlEQVR4nGP4z8AARAAI/gH/xp559wAAAABJRU5ErkJggg=='));
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), datosIntegrante(['foto' => $foto]))
        ->assertSessionHasErrors('foto');
    expect(Integrante::query()->count())->toBe(0)->and(Storage::disk('adjuntos')->allFiles())->toBeEmpty();
});

it('rechaza archivos peligrosos o con extensión incorrecta', function (string $nombre, string $contenido) {
    $archivo = UploadedFile::fake()->createWithContent($nombre, $contenido);
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), datosIntegrante([
        'foto' => new UploadedFile($archivo->getPathname(), $nombre, null, null, true),
    ]))->assertSessionHasErrors('foto');
    expect(Integrante::query()->count())->toBe(0)->and(Storage::disk('adjuntos')->allFiles())->toBeEmpty();
})->with([
    'html' => ['foto.png', '<html>contenido</html>'],
    'svg' => ['foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'],
    'extensión' => ['foto.txt', imagenIntegrante()],
]);

it('valida tamaño dimensiones y eliminación incompatible con foto nueva', function () {
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.store'), datosIntegrante(['foto' => UploadedFile::fake()->create('grande.png', 10241, 'image/png')]))->assertSessionHasErrors('foto');
    $ancha = substr_replace(imagenIntegrante(), pack('N', 6001), 16, 4);
    $this->post(route('academico.integrantes.store'), datosIntegrante(['foto' => UploadedFile::fake()->createWithContent('ancha.png', $ancha)]))->assertSessionHasErrors('foto');
    $this->post(route('academico.integrantes.store'), datosIntegrante(['foto' => fotoIntegrante(), 'eliminar_foto' => true]))->assertSessionHasErrors('foto');
});

it('revierte datos checklist y archivos si falla la auditoría', function () {
    TipoDocumentacionIntegrante::factory()->create();
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'integrantes_documentacion') {
            throw new RuntimeException('Falló auditoría checklist');
        }
    });
    expect(fn () => $this->post(route('academico.integrantes.store'), datosIntegrante(['foto' => fotoIntegrante()])))
        ->toThrow(RuntimeException::class, 'Falló auditoría checklist');
    expect(Integrante::query()->count())->toBe(0)->and(IntegranteDocumentacion::query()->count())->toBe(0)
        ->and(Log::query()->count())->toBe(0)->and(Storage::disk('adjuntos')->allFiles())->toBeEmpty();
});

it('consulta e imprime la ficha completa sin modificar datos', function () {
    $integrante = Integrante::factory()->create(['titulo_habilitante' => 'Profesora', 'numero_registro' => 'R1']);
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $integrante->documentacion()->create(['tipo_documentacion_integrante_id' => $tipo->id, 'presentado' => true]);
    $this->actingAs(User::factory()->create());
    foreach (['show', 'imprimir'] as $accion) {
        $this->get(route('academico.integrantes.'.$accion, $integrante))->assertInertia(fn (Assert $page) => $page
            ->component('academico/integrantes/'.$accion)->where('integrante.titulo_habilitante', 'Profesora')
            ->where('integrante.numero_registro', 'R1')->where('documentacion.0.presentado', true)->missing('integrante.foto_ruta'));
    }
    expect(Log::query()->count())->toBe(0);
});

it('mantiene la foto y los datos anteriores cuando falla un reemplazo', function () {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), datosIntegrante(['foto' => fotoIntegrante()]))->assertSessionHasNoErrors();
    $integrante = Integrante::query()->sole();
    $ruta = $integrante->foto_ruta;
    $this->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (): never {
        throw new RuntimeException('Fallo al reemplazar');
    });

    expect(fn () => $this->post(route('academico.integrantes.update', $integrante), datosIntegrante([
        '_method' => 'patch', 'apellido' => 'Cambio', 'foto' => fotoIntegrante(),
    ])))->toThrow(RuntimeException::class, 'Fallo al reemplazar');
    expect($integrante->fresh()->foto_ruta)->toBe($ruta)->and($integrante->fresh()->apellido)->toBe('Pérez')
        ->and(Storage::disk('adjuntos')->allFiles())->toHaveCount(1);
    Storage::disk('adjuntos')->assertExists($ruta);
});

it('permite conservar localidades y nacionalidades inactivas sin asignarlas a otros legajos', function () {
    $this->seed([ProvinciasYCiudadesSeeder::class, NacionalidadesSeeder::class]);
    $ciudad = Ciudad::query()->firstOrFail();
    $nacionalidad = Nacionalidad::query()->firstOrFail();
    $integrante = Integrante::factory()->create(['ciudad_id' => $ciudad->id, 'ciudad_nacimiento_id' => $ciudad->id, 'nacionalidad_id' => $nacionalidad->id]);
    $ciudad->update(['activo' => false]);
    $nacionalidad->update(['activo' => false]);
    $datos = datosIntegrante(['legajo' => $integrante->legajo, 'ciudad_id' => $ciudad->id, 'ciudad_nacimiento_id' => $ciudad->id, 'nacionalidad_id' => $nacionalidad->id]);
    $this->actingAs(User::factory()->create())->patch(route('academico.integrantes.update', $integrante), $datos)->assertSessionHasNoErrors();
    $this->post(route('academico.integrantes.store'), [...$datos, 'legajo' => 'NUEVO'])->assertSessionHasErrors(['ciudad_id', 'ciudad_nacimiento_id', 'nacionalidad_id']);
    $this->get(route('academico.integrantes.show', $integrante))->assertInertia(fn (Assert $page) => $page
        ->where('integrante.ciudad.id', $ciudad->id)->where('integrante.ciudad_nacimiento.id', $ciudad->id)
        ->where('integrante.nacionalidad.id', $nacionalidad->id));
});

it('respeta policies para consultar crear modificar estado y fotos', function () {
    $integrante = Integrante::factory()->create();
    $this->actingAs(User::factory()->create());
    Gate::before(fn (): bool => false);
    foreach (['index', 'create', 'show', 'edit', 'imprimir', 'foto'] as $accion) {
        $this->get(route('academico.integrantes.'.$accion, in_array($accion, ['index', 'create']) ? [] : $integrante))->assertForbidden();
    }
    $this->post(route('academico.integrantes.store'), datosIntegrante())->assertForbidden();
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.'.$accion, $integrante), datosIntegrante())->assertForbidden();
    }
});

it('carga un integrante de ejemplo sin duplicarlo ni modificarlo al repetir el seeder', function () {
    $this->seed(IntegranteSeeder::class);
    $integrante = Integrante::query()->sole();
    $integrante->update(['apellido' => 'Editado', 'activo' => false]);
    $this->seed(IntegranteSeeder::class);
    expect(Integrante::query()->count())->toBe(1)->and($integrante->fresh()->apellido)->toBe('Editado')->and($integrante->fresh()->activo)->toBeFalse();
});
