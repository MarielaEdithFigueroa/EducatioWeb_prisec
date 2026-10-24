<?php

use App\Models\Adjunto;
use App\Models\Alumno;
use App\Models\Log;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('adjuntos');
});

function alumnoDocumentacion(): Alumno
{
    return Alumno::query()->create([
        'legajo' => fake()->unique()->numerify('DOC-########'),
        'apellido' => 'Pérez',
        'nombre' => 'Ana',
    ]);
}

/** @param array<string, mixed> $atributos */
function documentoDeAlumno(Alumno $alumno, array $atributos = []): Adjunto
{
    $adjunto = Adjunto::factory()->create($atributos);
    $alumno->adjuntos()->attach($adjunto);
    Storage::disk('adjuntos')->put($adjunto->ruta, 'Contenido del documento');

    return $adjunto;
}

it('requiere autenticación en todas las operaciones de documentación', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno);
    $parametros = ['alumno' => $alumno, 'adjunto' => $adjunto];

    $this->get(route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'documentacion']))
        ->assertRedirect(route('login'));
    $this->post(route('academico.alumnos.documentacion.store', $alumno))->assertRedirect(route('login'));
    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))->assertRedirect(route('login'));
    $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))->assertRedirect(route('login'));

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.alumnos.documentacion.'.$accion, $parametros))->assertRedirect(route('login'));
    }
});

it('guarda el archivo privado y su vínculo con auditoría sin aceptar metadatos del cliente', function () {
    $this->travelTo(now()->setDate(2026, 10, 2)->setTime(14, 35, 27));
    $alumno = alumnoDocumentacion();
    $usuario = User::factory()->create();
    $archivo = UploadedFile::fake()->create('Constancia escolar.pdf', 12, 'application/pdf');

    $this->actingAs($usuario)
        ->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => $archivo,
            'observaciones' => 'Documentación presentada',
            'disco' => 'public',
            'ruta' => '../../archivo.php',
            'usuario_id' => 999999,
            'activo' => false,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'documentacion']));

    $adjunto = $alumno->adjuntos()->sole();
    expect($adjunto->activo)->toBeTrue()
        ->and($adjunto->nombre_original)->toBe('Constancia escolar.pdf')
        ->and($adjunto->disco)->toBe('adjuntos')
        ->and(basename($adjunto->ruta))->toBe('20261002_143527_Constancia_escolar_'.sprintf('%06d', $adjunto->id).'.pdf')
        ->and($adjunto->mime)->toBe('application/pdf')
        ->and($adjunto->tamano_bytes)->toBe(12 * 1024)
        ->and($adjunto->usuario_id)->toBe($usuario->id)
        ->and($adjunto->observaciones)->toBe('Documentación presentada');
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
    $this->assertDatabaseHas('alumnos_adjuntos', ['alumno_id' => $alumno->id, 'adjunto_id' => $adjunto->id]);

    $log = Log::query()->where('entidad', 'adjuntos')->sole();
    expect($log->accion)->toBe('create')
        ->and($log->usuario_id)->toBe($usuario->id)
        ->and($log->entidad_id)->toBe($adjunto->id)
        ->and($log->nuevo['nombre_original'])->toBe('Constancia escolar.pdf')
        ->and($log->nuevo['vinculo'])->toBe(['entidad' => 'alumnos', 'entidad_id' => $alumno->id])
        ->and($log->nuevo['ruta'])->toBe($adjunto->ruta);
});

it('usa el id asignado con un mínimo de seis posiciones sin truncarlo', function (int $anterior, string $sufijo) {
    $this->travelTo(now()->setDate(2026, 10, 2)->setTime(14, 35, 27));
    Adjunto::factory()->create(['id' => $anterior]);
    $alumno = alumnoDocumentacion();

    $this->actingAs(User::factory()->create())
        ->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => UploadedFile::fake()->create('Certificado médico.PDF', 1, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertRedirect();

    $adjunto = $alumno->adjuntos()->sole();
    expect($adjunto->id)->toBe($anterior + 1)
        ->and(basename($adjunto->ruta))->toBe('20261002_143527_Certificado_médico_'.$sufijo.'.PDF');
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
})->with([
    'rellena con ceros' => [122, '000123'],
    'seis dígitos' => [999998, '999999'],
    'más de seis dígitos' => [999999, '1000000'],
]);

it('conserva ambos contenidos al adjuntar dos archivos con el mismo nombre', function () {
    $this->freezeTime();
    $alumno = alumnoDocumentacion();
    $this->actingAs(User::factory()->create());
    $contenidos = ["%PDF-1.4\nPrimera constancia", "%PDF-1.4\nSegunda constancia"];

    foreach ($contenidos as $contenido) {
        $this->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => UploadedFile::fake()->createWithContent('Constancia.pdf', $contenido),
        ])->assertSessionHasNoErrors()->assertRedirect();
    }

    $adjuntos = $alumno->adjuntos()->orderBy('adjuntos.id')->get();
    expect($adjuntos)->toHaveCount(2)
        ->and($adjuntos->pluck('ruta')->unique())->toHaveCount(2)
        ->and(dirname($adjuntos[0]->ruta))->not->toBe(dirname($adjuntos[1]->ruta));

    foreach ($adjuntos as $indice => $adjunto) {
        expect($adjunto->nombre_original)->toBe('Constancia.pdf')
            ->and(basename($adjunto->ruta))->toBe(now()->format('Ymd_His').'_Constancia_'.sprintf('%06d', $adjunto->id).'.pdf')
            ->and(Storage::disk('adjuntos')->get($adjunto->ruta))->toBe($contenidos[$indice]);

        $this->get(route('academico.alumnos.documentacion.descargar', ['alumno' => $alumno, 'adjunto' => $adjunto]))
            ->assertDownload('Constancia.pdf')
            ->assertStreamedContent($contenidos[$indice]);
    }
});

it('genera nombres legibles seguros y acotados para el almacenamiento', function (string $nombre, string $prefijo) {
    $this->travelTo(now()->setDate(2026, 10, 2)->setTime(14, 35, 27));
    $alumno = alumnoDocumentacion();
    $this->actingAs(User::factory()->create())
        ->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => UploadedFile::fake()->create($nombre, 1, 'application/pdf'),
        ])->assertSessionHasNoErrors()->assertRedirect();

    $adjunto = $alumno->adjuntos()->sole();
    $nombreGuardado = basename($adjunto->ruta);
    expect($nombreGuardado)->toStartWith('20261002_143527_'.$prefijo)
        ->and($nombreGuardado)->toEndWith('_'.sprintf('%06d', $adjunto->id).'.'.pathinfo($nombre, PATHINFO_EXTENSION))
        ->and(preg_match('/[<>:"\/\\\\|?*\x00-\x1F\x7F]/u', $nombreGuardado))->toBe(0)
        ->and(mb_check_encoding($nombreGuardado, 'UTF-8'))->toBeTrue()
        ->and(strlen($nombreGuardado))->toBeLessThanOrEqual(167)
        ->and(substr_count($adjunto->ruta, '/'))->toBe(2);
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
})->with([
    'acentos y espacios' => ['Certificado médico.pdf', 'Certificado_médico_'],
    'extensión original' => ['Certificado médico.PDF', 'Certificado_médico_'],
    'varios puntos' => ['Informe.final.PdF', 'Informe.final_'],
    'caracteres reservados' => ['Informe: "anual"?.pdf', 'Informe___anual___'],
    'ruta de Windows' => ['..\\carpeta\\dni.pdf', 'dni_'],
    'nombre reservado' => ['CON.pdf', 'CON_'],
    'nombre largo' => [str_repeat('á', 110).'.pdf', str_repeat('á', 70).'_'],
    'base vacía' => ['.pdf', 'archivo_'],
]);

it('muestra documentos activos e históricos sólo del alumno sin revelar rutas privadas', function () {
    $alumno = alumnoDocumentacion();
    $activo = documentoDeAlumno($alumno, ['observaciones' => 'Constancia']);
    documentoDeAlumno($alumno, ['activo' => false]);
    documentoDeAlumno(alumnoDocumentacion());

    $this->actingAs(User::factory()->create())
        ->get(route('academico.alumnos.show', ['alumno' => $alumno, 'seccion' => 'documentacion']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('academico/alumnos/show')
            ->where('seccion', 'documentacion')
            ->has('documentacion', 2)
            ->where('documentacion.0.id', $activo->id)
            ->where('documentacion.0.observaciones', 'Constancia')
            ->where('documentacion.0.usuario', $activo->usuario->login)
            ->where('documentacion.1.activo', false)
            ->missing('documentacion.0.ruta')
            ->missing('documentacion.0.disco')
            ->missing('documentacion.0.pivot')
            ->where('configAdjuntos.max_bytes', 10240 * 1024)
        );
});

it('rechaza archivos y observaciones inválidos sin escribir archivos ni registros', function (string $caso) {
    $datos = ['observaciones' => 'Prueba'];

    if ($caso === 'excedido') {
        $datos['archivo'] = UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf');
    } elseif ($caso === 'contenido') {
        $datos['archivo'] = UploadedFile::fake()->create('aparenta.pdf', 1, 'text/html');
    } elseif ($caso === 'extension') {
        $datos['archivo'] = UploadedFile::fake()->create('archivo.html', 1, 'application/pdf');
    } elseif ($caso === 'observaciones') {
        $datos['archivo'] = UploadedFile::fake()->create('documento.pdf', 1, 'application/pdf');
        $datos['observaciones'] = str_repeat('a', 2001);
    }

    $this->actingAs(User::factory()->create())
        ->post(route('academico.alumnos.documentacion.store', alumnoDocumentacion()), $datos)
        ->assertSessionHasErrors($caso === 'observaciones' ? 'observaciones' : 'archivo');

    expect(Adjunto::query()->count())->toBe(0)
        ->and(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0)
        ->and(Storage::disk('adjuntos')->allFiles())->toBe([]);
})->with(['ausente', 'excedido', 'contenido', 'extension', 'observaciones']);

it('descarga el contenido con el nombre original y cabeceras privadas', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno, ['nombre_original' => 'Constancia.pdf']);

    $respuesta = $this->actingAs(User::factory()->create())
        ->get(route('academico.alumnos.documentacion.descargar', ['alumno' => $alumno, 'adjunto' => $adjunto]));

    $respuesta->assertOk()
        ->assertDownload('Constancia.pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Content-Type', 'application/octet-stream')
        ->assertStreamedContent('Contenido del documento');
    expect($respuesta->headers->get('Cache-Control'))->toContain('private', 'no-store');
});

it('muestra la imagen original inline con el tipo detectado y cabeceras privadas', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno, ['nombre_original' => 'Foto.png', 'mime' => 'image/png']);
    $contenido = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
    Storage::disk('adjuntos')->put($adjunto->ruta, $contenido);
    $this->actingAs(User::factory()->create());
    $parametros = ['alumno' => $alumno, 'adjunto' => $adjunto];

    $respuesta = $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertStreamedContent($contenido);

    expect($respuesta->headers->get('Content-Disposition'))->toStartWith('inline;')
        ->and($respuesta->headers->get('Cache-Control'))->toContain('private', 'no-store');

    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))
        ->assertDownload('Foto.png')
        ->assertHeader('Content-Type', 'application/octet-stream')
        ->assertStreamedContent($contenido);

    $adjunto->update(['activo' => false]);
    $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))->assertNotFound();
    $adjunto->update(['activo' => true]);
    Storage::disk('adjuntos')->delete($adjunto->ruta);
    $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))->assertNotFound();
});

it('no sirve contenido no permitido como miniatura aunque los metadatos indiquen imagen', function (string $contenido) {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno, ['nombre_original' => 'Foto.jpg', 'mime' => 'image/jpeg']);
    Storage::disk('adjuntos')->put($adjunto->ruta, $contenido);

    $this->actingAs(User::factory()->create())
        ->get(route('academico.alumnos.documentacion.miniatura', ['alumno' => $alumno, 'adjunto' => $adjunto]))
        ->assertNotFound();
})->with([
    'pdf' => ["%PDF-1.4\nDocumento"],
    'svg' => ['<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
    'html' => ['<!DOCTYPE html><html><body>Documento</body></html>'],
    'texto' => ['Documento de texto'],
]);

it('rechaza un adjunto de otro alumno en todas sus operaciones', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno(alumnoDocumentacion());
    $parametros = ['alumno' => $alumno, 'adjunto' => $adjunto];
    $this->actingAs(User::factory()->create());

    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))->assertNotFound();
    $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))->assertNotFound();

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.alumnos.documentacion.'.$accion, $parametros), ['observaciones' => 'No modificar'])
            ->assertNotFound();
    }

    expect($adjunto->refresh()->observaciones)->toBeNull()
        ->and($adjunto->activo)->toBeTrue()
        ->and(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0);
});

it('aplica las policies aunque el documento pertenezca al alumno', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno);
    $parametros = ['alumno' => $alumno, 'adjunto' => $adjunto];
    Gate::before(fn (User $usuario, string $habilidad) => false);
    $this->actingAs(User::factory()->create());

    $this->post(route('academico.alumnos.documentacion.store', $alumno))->assertForbidden();
    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))->assertForbidden();
    $this->get(route('academico.alumnos.documentacion.miniatura', $parametros))->assertForbidden();
    $this->patch(route('academico.alumnos.documentacion.update', $parametros), ['observaciones' => 'Cambio'])->assertForbidden();
    $this->patch(route('academico.alumnos.documentacion.desactivar', $parametros))->assertForbidden();
    $this->patch(route('academico.alumnos.documentacion.reactivar', $parametros))->assertForbidden();
});

it('actualiza sólo observaciones con auditoría y no reemplaza el archivo', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno, ['observaciones' => 'Antes']);
    $ruta = $adjunto->ruta;
    $this->actingAs(User::factory()->create())
        ->patch(route('academico.alumnos.documentacion.update', ['alumno' => $alumno, 'adjunto' => $adjunto]), [
            'observaciones' => 'Después',
            'nombre_original' => 'Reemplazo.exe',
            'ruta' => 'otra',
            'activo' => false,
        ])->assertSessionHasNoErrors()->assertRedirect();

    expect($adjunto->refresh()->observaciones)->toBe('Después')
        ->and($adjunto->nombre_original)->toBe('documento.pdf')
        ->and($adjunto->ruta)->toBe($ruta)
        ->and($adjunto->activo)->toBeTrue();
    expect(Storage::disk('adjuntos')->get($ruta))->toBe('Contenido del documento');
    $log = Log::query()->where('entidad', 'adjuntos')->sole();
    expect($log->accion)->toBe('update')
        ->and($log->anterior['observaciones'])->toBe('Antes')
        ->and($log->nuevo['observaciones'])->toBe('Después');
});

it('valida observaciones al editar y permite dejarlas vacías', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno, ['observaciones' => 'Antes']);
    $url = route('academico.alumnos.documentacion.update', ['alumno' => $alumno, 'adjunto' => $adjunto]);
    $this->actingAs(User::factory()->create());

    $this->patch($url, [])->assertSessionHasErrors('observaciones');
    $this->patch($url, ['observaciones' => str_repeat('a', 2001)])->assertSessionHasErrors('observaciones');
    expect($adjunto->refresh()->observaciones)->toBe('Antes');

    $this->patch($url, ['observaciones' => ''])->assertSessionHasNoErrors()->assertRedirect();
    expect($adjunto->refresh()->observaciones)->toBeNull();
});

it('desactiva y reactiva conservando archivo y vínculo sin duplicar la auditoría', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno);
    $parametros = ['alumno' => $alumno, 'adjunto' => $adjunto];
    $this->actingAs(User::factory()->create());

    $this->patch(route('academico.alumnos.documentacion.desactivar', $parametros))->assertRedirect();
    $this->patch(route('academico.alumnos.documentacion.desactivar', $parametros))->assertRedirect();
    expect($adjunto->refresh()->activo)->toBeFalse();
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))->assertNotFound();
    $this->patch(route('academico.alumnos.documentacion.update', $parametros), ['observaciones' => 'Cambio'])->assertNotFound();

    $this->patch(route('academico.alumnos.documentacion.reactivar', $parametros))->assertRedirect();
    $this->patch(route('academico.alumnos.documentacion.reactivar', $parametros))->assertRedirect();
    $this->get(route('academico.alumnos.documentacion.descargar', $parametros))->assertOk();
    expect($adjunto->refresh()->activo)->toBeTrue()
        ->and($alumno->adjuntos()->count())->toBe(1)
        ->and(Log::query()->where('entidad', 'adjuntos')->orderBy('id')->pluck('accion')->all())
        ->toBe(['deactivate', 'reactivate']);
});

it('responde no encontrado cuando el archivo falta en el almacenamiento', function () {
    $alumno = alumnoDocumentacion();
    $adjunto = documentoDeAlumno($alumno);
    Storage::disk('adjuntos')->delete($adjunto->ruta);

    $this->actingAs(User::factory()->create())
        ->get(route('academico.alumnos.documentacion.descargar', ['alumno' => $alumno, 'adjunto' => $adjunto]))
        ->assertNotFound();
});

it('revierte metadatos y vínculo y limpia el archivo si falla la auditoría', function () {
    $alumno = alumnoDocumentacion();
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    $evento = 'eloquent.creating: '.Log::class;
    Event::listen($evento, function (): void {
        throw new RuntimeException('Fallo de auditoría simulado');
    });

    try {
        expect(fn () => $this->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
        ]))->toThrow(RuntimeException::class, 'Fallo de auditoría simulado');
    } finally {
        Event::forget($evento);
    }

    expect(Adjunto::query()->count())->toBe(0)
        ->and(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0)
        ->and($alumno->adjuntos()->count())->toBe(0)
        ->and(Storage::disk('adjuntos')->allFiles())->toBe([]);
    $this->assertDatabaseCount('alumnos_adjuntos', 0);
});

it('no escribe archivos si falla la creación del adjunto', function () {
    $alumno = alumnoDocumentacion();
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    $disco = Mockery::mock(FilesystemAdapter::class);
    $disco->shouldNotReceive('putFileAs');
    $disco->shouldNotReceive('delete');
    Storage::set('adjuntos', $disco);
    $evento = 'eloquent.creating: '.Adjunto::class;
    Event::listen($evento, function (): void {
        throw new RuntimeException('Fallo al obtener el ID');
    });

    try {
        expect(fn () => $this->post(route('academico.alumnos.documentacion.store', $alumno), [
            'archivo' => UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
        ]))->toThrow(RuntimeException::class, 'Fallo al obtener el ID');
    } finally {
        Event::forget($evento);
    }

    $this->assertDatabaseCount('adjuntos', 0);
    $this->assertDatabaseCount('alumnos_adjuntos', 0);
    expect(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0);
});

it('revierte los metadatos si el almacenamiento no puede guardar el archivo', function () {
    $alumno = alumnoDocumentacion();
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    $disco = Mockery::mock(FilesystemAdapter::class);
    $disco->shouldReceive('putFileAs')->once()->andReturn(false);
    $disco->shouldReceive('delete')->once()->andReturn(true);
    Storage::set('adjuntos', $disco);

    expect(fn () => $this->post(route('academico.alumnos.documentacion.store', $alumno), [
        'archivo' => UploadedFile::fake()->create('archivo.pdf', 1, 'application/pdf'),
    ]))->toThrow(RuntimeException::class, 'No se pudo guardar el archivo adjunto.');

    expect(Adjunto::query()->count())->toBe(0)
        ->and(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0);
    $this->assertDatabaseCount('alumnos_adjuntos', 0);
});
