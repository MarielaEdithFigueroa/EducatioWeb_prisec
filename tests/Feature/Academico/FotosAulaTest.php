<?php

use App\Models\Adjunto;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Log;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('adjuntos');
});

function contenidoFotoAula(): string
{
    return base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=');
}

function fotoDeAula(Aula $aula, bool $activo = true): Adjunto
{
    $foto = Adjunto::factory()->create(['activo' => $activo, 'nombre_original' => 'Aula 2.png', 'mime' => 'image/png']);
    $aula->adjuntos()->attach($foto);
    Storage::disk('adjuntos')->put($foto->ruta, contenidoFotoAula());

    return $foto;
}

it('requiere autenticación para todas las operaciones de fotos', function () {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $parametros = ['aula' => $aula, 'adjunto' => $foto];
    $this->post(route('academico.aulas.fotos.store', $aula))->assertRedirect(route('login'));

    foreach (['miniatura', 'descargar'] as $accion) {
        $this->get(route('academico.aulas.fotos.'.$accion, $parametros))->assertRedirect(route('login'));
    }

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.aulas.fotos.'.$accion, $parametros))->assertRedirect(route('login'));
    }
});

it('adjunta varias fotos con el mismo nombre en privado con auditoría y nombres únicos', function () {
    $aula = Aula::factory()->create();
    $usuario = User::factory()->create();
    $this->travelTo(now()->setDate(2026, 10, 19)->setTime(10, 30, 45));
    $this->actingAs($usuario);

    foreach (['Frente', 'Fondo'] as $descripcion) {
        $this->post(route('academico.aulas.fotos.store', $aula), [
            'archivo' => UploadedFile::fake()->createWithContent('Aula 2.png', contenidoFotoAula()),
            'observaciones' => $descripcion,
            'disco' => 'public', 'ruta' => '../../foto.png', 'usuario_id' => 999999, 'activo' => false,
        ])->assertSessionHasNoErrors()->assertRedirect(route('academico.aulas.show', $aula));
    }

    $fotos = $aula->adjuntos()->orderBy('adjuntos.id')->get();
    expect($fotos)->toHaveCount(2)->and($fotos->pluck('ruta')->unique())->toHaveCount(2);

    foreach ($fotos as $foto) {
        expect($foto->activo)->toBeTrue()
            ->and($foto->disco)->toBe('adjuntos')->and($foto->mime)->toBe('image/png')
            ->and($foto->usuario_id)->toBe($usuario->id)
            ->and(basename($foto->ruta))->toBe('20261019_103045_Aula_2_'.sprintf('%06d', $foto->id).'.png')
            ->and($foto->aulas()->sole()->id)->toBe($aula->id);
        Storage::disk('adjuntos')->assertExists($foto->ruta);
        $log = Log::query()->where('entidad', 'adjuntos')->where('entidad_id', $foto->id)->sole();
        expect($log->accion)->toBe('create')
            ->and($log->nuevo['vinculo'])->toBe(['entidad' => 'aulas', 'entidad_id' => $aula->id]);
    }

    expect($fotos->pluck('observaciones')->all())->toBe(['Frente', 'Fondo']);
});

it('expone sólo las fotos del aula sin rutas privadas y con su configuración', function () {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $inactiva = fotoDeAula($aula, false);
    fotoDeAula(Aula::factory()->create());
    $this->actingAs(User::factory()->create())->get(route('academico.aulas.show', $aula))
        ->assertInertia(fn (Assert $page) => $page->component('academico/aulas/show')
            ->has('fotos', 2)->where('fotos.0.id', $foto->id)->where('fotos.1.id', $inactiva->id)
            ->where('fotos.1.activo', false)
            ->missing('fotos.0.ruta')->missing('fotos.0.disco')->missing('fotos.0.pivot')
            ->where('configFotos.max_bytes', 10485760)
            ->where('configFotos.extensiones', ['jpg', 'jpeg', 'png', 'webp']));
});

it('rechaza documentos y contenido que no sea una foto incluso renombrado', function (string $nombre, string $contenido) {
    $aula = Aula::factory()->create();
    $archivo = UploadedFile::fake()->createWithContent($nombre, $contenido);
    $this->actingAs(User::factory()->create())->post(route('academico.aulas.fotos.store', $aula), [
        'archivo' => new UploadedFile($archivo->getPathname(), $nombre, null, null, true),
    ])->assertSessionHasErrors('archivo');
    $this->assertDatabaseCount('adjuntos', 0);
    expect(Storage::disk('adjuntos')->allFiles())->toBeEmpty();
})->with([
    'pdf' => ['documento.pdf', "%PDF-1.4\nDocumento"],
    'pdf renombrado' => ['foto.jpg', "%PDF-1.4\nDocumento"],
    'html renombrado' => ['foto.png', '<html><script>alert(1)</script></html>'],
    'svg' => ['foto.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'],
    'imagen con extensión no permitida' => ['foto.txt', contenidoFotoAula()],
]);

it('valida archivo requerido tamaño máximo y descripción', function () {
    $aula = Aula::factory()->create();
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.aulas.fotos.store', $aula), [])->assertSessionHasErrors('archivo');
    $this->post(route('academico.aulas.fotos.store', $aula), [
        'archivo' => UploadedFile::fake()->create('grande.png', 10241, 'image/png'),
    ])->assertSessionHasErrors('archivo');
    $this->post(route('academico.aulas.fotos.store', $aula), [
        'archivo' => UploadedFile::fake()->createWithContent('foto.png', contenidoFotoAula()),
        'observaciones' => str_repeat('x', 2001),
    ])->assertSessionHasErrors('observaciones');
    $this->assertDatabaseCount('adjuntos', 0);
});

it('sirve imágenes privadas y descargas con el nombre original', function () {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $parametros = ['aula' => $aula, 'adjunto' => $foto];
    $this->actingAs(User::factory()->create());
    $this->get(route('academico.aulas.fotos.miniatura', $parametros))->assertOk()
        ->assertHeader('Content-Type', 'image/png')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'no-store, private')->assertStreamedContent(contenidoFotoAula());
    $this->get(route('academico.aulas.fotos.descargar', $parametros))
        ->assertDownload('Aula 2.png')->assertStreamedContent(contenidoFotoAula());
    Storage::disk('adjuntos')->put($foto->ruta, '<html>Contenido alterado</html>');
    $this->get(route('academico.aulas.fotos.miniatura', $parametros))->assertNotFound();
    Storage::disk('adjuntos')->delete($foto->ruta);
    $this->get(route('academico.aulas.fotos.descargar', $parametros))->assertNotFound();
    $this->get(route('academico.aulas.fotos.miniatura', $parametros))->assertNotFound();
});

it('impide acceder o modificar adjuntos de otra aula o de alumnos', function () {
    $aula = Aula::factory()->create();
    $otraFoto = fotoDeAula(Aula::factory()->create());
    $documento = Adjunto::factory()->create();
    $alumno = Alumno::query()->create(['legajo' => 'FOTO-1', 'apellido' => 'Pérez', 'nombre' => 'Ana']);
    $alumno->adjuntos()->attach($documento);
    $this->actingAs(User::factory()->create());

    foreach ([$otraFoto, $documento] as $adjunto) {
        $parametros = ['aula' => $aula, 'adjunto' => $adjunto];

        foreach (['miniatura', 'descargar'] as $accion) {
            $this->get(route('academico.aulas.fotos.'.$accion, $parametros))->assertNotFound();
        }

        foreach (['update', 'desactivar', 'reactivar'] as $accion) {
            $this->patch(route('academico.aulas.fotos.'.$accion, $parametros), ['observaciones' => 'Ajena'])->assertNotFound();
        }
    }

    expect(Log::query()->count())->toBe(0);
});

it('actualiza únicamente la descripción y audita cambios reales', function () {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $rutaOriginal = $foto->ruta;
    $url = route('academico.aulas.fotos.update', ['aula' => $aula, 'adjunto' => $foto]);
    $this->actingAs(User::factory()->create());
    $this->patch($url, ['observaciones' => str_repeat('x', 2001)])->assertSessionHasErrors('observaciones');

    foreach ([1, 2] as $intento) {
        $this->patch($url, ['observaciones' => 'Vista al patio', 'activo' => false, 'ruta' => 'otro.png'])
            ->assertSessionHasNoErrors()->assertRedirect(route('academico.aulas.show', $aula));
    }

    expect($foto->refresh()->observaciones)->toBe('Vista al patio')->and($foto->activo)->toBeTrue()
        ->and($foto->ruta)->toBe($rutaOriginal)->and(Log::query()->sole()->accion)->toBe('update');
});

it('desactiva y reactiva sin eliminar archivos ni duplicar auditorías', function () {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $parametros = ['aula' => $aula, 'adjunto' => $foto];
    $this->actingAs(User::factory()->create());

    foreach ([1, 2] as $intento) {
        $this->patch(route('academico.aulas.fotos.desactivar', $parametros))->assertRedirect();
    }

    expect($foto->refresh()->activo)->toBeFalse();
    Storage::disk('adjuntos')->assertExists($foto->ruta);
    $this->get(route('academico.aulas.fotos.miniatura', $parametros))->assertNotFound();
    $this->get(route('academico.aulas.fotos.descargar', $parametros))->assertNotFound();
    $this->patch(route('academico.aulas.fotos.update', $parametros), ['observaciones' => 'No'])->assertNotFound();

    foreach ([1, 2] as $intento) {
        $this->patch(route('academico.aulas.fotos.reactivar', $parametros))->assertRedirect();
    }

    expect($foto->refresh()->activo)->toBeTrue()->and($aula->adjuntos()->count())->toBe(1)
        ->and(Log::query()->orderBy('id')->pluck('accion')->all())->toBe(['deactivate', 'reactivate']);
    $this->get(route('academico.aulas.fotos.miniatura', $parametros))->assertOk();
});

it('respeta los permisos del aula y del adjunto', function (string $clase) {
    $aula = Aula::factory()->create();
    $foto = fotoDeAula($aula);
    $parametros = ['aula' => $aula, 'adjunto' => $foto];
    Gate::before(fn (User $user, string $ability, array $arguments) => isset($arguments[0]) && ($arguments[0] === $clase || $arguments[0] instanceof $clase) ? false : null);
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.aulas.fotos.store', $aula))->assertForbidden();

    foreach (['miniatura', 'descargar'] as $accion) {
        $this->get(route('academico.aulas.fotos.'.$accion, $parametros))->assertForbidden();
    }

    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.aulas.fotos.'.$accion, $parametros))->assertForbidden();
    }
})->with(['aula' => [Aula::class], 'adjunto' => [Adjunto::class]]);

it('revierte archivo y vínculo si falla la auditoría de la carga', function () {
    $aula = Aula::factory()->create();
    $this->actingAs(User::factory()->create());
    Event::listen('eloquent.creating: '.Log::class, fn () => throw new RuntimeException('Fallo de auditoría'));

    try {
        $this->post(route('academico.aulas.fotos.store', $aula), [
            'archivo' => UploadedFile::fake()->createWithContent('foto.png', contenidoFotoAula()),
        ])->assertServerError();
    } finally {
        Event::forget('eloquent.creating: '.Log::class);
    }

    $this->assertDatabaseCount('adjuntos', 0);
    $this->assertDatabaseCount('aulas_adjuntos', 0);
    expect(Storage::disk('adjuntos')->allFiles())->toBeEmpty();
});
