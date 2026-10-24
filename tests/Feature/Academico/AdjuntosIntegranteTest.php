<?php

use App\Models\Adjunto;
use App\Models\Alumno;
use App\Models\Aula;
use App\Models\Integrante;
use App\Models\Log;
use App\Models\TipoDocumentacionIntegrante;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('adjuntos');
    $this->integrante = Integrante::factory()->create();
});

function adjuntoLibreIntegrante(Integrante $integrante): Adjunto
{
    $adjunto = Adjunto::factory()->create();
    $integrante->adjuntos()->attach($adjunto);
    Storage::disk('adjuntos')->put($adjunto->ruta, 'Contenido privado');

    return $adjunto;
}

it('requiere autenticación en todas las operaciones de adjuntos libres', function () {
    $adjunto = adjuntoLibreIntegrante($this->integrante);
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    $this->post(route('academico.integrantes.adjuntos.store', $this->integrante))->assertRedirect(route('login'));
    foreach (['descargar', 'miniatura'] as $accion) {
        $this->get(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertRedirect(route('login'));
    }
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertRedirect(route('login'));
    }
});

it('adjunta archivos privados independientes del checklist y vuelve a la pantalla de origen', function (string $pantalla) {
    $usuario = User::factory()->create();
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $origen = route('academico.integrantes.'.$pantalla, [
        'integrante' => $this->integrante, 'seccion' => 'adjuntos', 'filtros' => ['buscar' => 'Ana'],
    ]);
    $this->actingAs($usuario)->from($origen)->post(route('academico.integrantes.adjuntos.store', $this->integrante), [
        'archivo' => UploadedFile::fake()->create('Contrato.pdf', 12, 'application/pdf'),
        'observaciones' => 'Acuerdo firmado', 'disco' => 'public', 'ruta' => '../../archivo', 'usuario_id' => 99999,
    ])->assertSessionHasNoErrors()->assertRedirect($origen);
    $adjunto = $this->integrante->adjuntos()->sole();
    expect($adjunto->disco)->toBe('adjuntos')->and($adjunto->usuario_id)->toBe($usuario->id)
        ->and($adjunto->observaciones)->toBe('Acuerdo firmado')->and($adjunto->activo)->toBeTrue()
        ->and($this->integrante->documentacion()->count())->toBe(0);
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
    expect(Log::query()->where('entidad', 'adjuntos')->sole()->nuevo['vinculo'])
        ->toBe(['entidad' => 'integrantes', 'entidad_id' => $this->integrante->id]);
    $this->get($origen)->assertInertia(fn (Assert $page) => $page
        ->where('seccion', 'adjuntos')->has('adjuntosLibres', 1)
        ->where('adjuntosLibres.0.id', $adjunto->id)->where('adjuntosLibres.0.usuario', $usuario->login)
        ->missing('adjuntosLibres.0.ruta')->missing('adjuntosLibres.0.disco')
        ->where('documentacion.0.tipo_documentacion_integrante_id', $tipo->id)
        ->where('documentacion.0.presentado', false)->where('documentacion.0.adjunto', null)
        ->where('configAdjuntos.extensiones', config('adjuntos.extensiones')));
    $this->get(route('academico.integrantes.adjuntos.descargar', ['integrante' => $this->integrante, 'adjunto' => $adjunto]))
        ->assertDownload('Contrato.pdf')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'no-store, private');
})->with(['show', 'edit']);

it('impide acceder o modificar adjuntos de otro vínculo incluso PDFs del mismo legajo', function (string $vinculo) {
    $adjunto = Adjunto::factory()->create();
    $destino = match ($vinculo) {
        'otro integrante' => Integrante::factory()->create()->adjuntos(),
        'alumno' => Alumno::query()->create(['legajo' => 'A1', 'nombre' => 'Ana', 'apellido' => 'Pérez'])->adjuntos(),
        'aula' => Aula::factory()->create()->adjuntos(),
        'checklist' => $this->integrante->documentacion()->create([
            'tipo_documentacion_integrante_id' => TipoDocumentacionIntegrante::factory()->create()->id,
            'presentado' => true,
        ])->adjuntos(),
    };
    $destino->attach($adjunto);
    $this->actingAs(User::factory()->create());
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    foreach (['descargar', 'miniatura'] as $accion) {
        $this->get(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertNotFound();
    }
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.adjuntos.'.$accion, $parametros), ['observaciones' => 'Cambio'])->assertNotFound();
    }
    expect($adjunto->fresh()->activo)->toBeTrue()->and($adjunto->fresh()->observaciones)->toBe($adjunto->observaciones);
})->with(['otro integrante', 'alumno', 'aula', 'checklist']);

it('actualiza observaciones y conserva historial con bajas y reactivaciones idempotentes', function () {
    $adjunto = adjuntoLibreIntegrante($this->integrante);
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    $this->actingAs(User::factory()->create());
    for ($i = 0; $i < 2; $i++) {
        $this->patch(route('academico.integrantes.adjuntos.update', $parametros), ['observaciones' => 'Actualizado', 'ruta' => 'otra'])
            ->assertSessionHasNoErrors();
    }
    foreach (['desactivar' => false, 'reactivar' => true] as $accion => $activo) {
        for ($i = 0; $i < 2; $i++) {
            $this->patch(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertSessionHasNoErrors();
        }
        expect($adjunto->fresh()->activo)->toBe($activo);
        if (! $activo) {
            $this->get(route('academico.integrantes.adjuntos.descargar', $parametros))->assertNotFound();
            $this->patch(route('academico.integrantes.adjuntos.update', $parametros), ['observaciones' => 'Inactivo'])->assertNotFound();
            $this->get(route('academico.integrantes.show', $this->integrante))->assertInertia(fn (Assert $page) => $page
                ->where('adjuntosLibres.0.activo', false));
        }
    }
    expect($adjunto->fresh()->observaciones)->toBe('Actualizado')->and($adjunto->fresh()->ruta)->toBe($adjunto->ruta)
        ->and(Log::query()->where('entidad', 'adjuntos')->orderBy('id')->pluck('accion')->all())->toBe(['update', 'deactivate', 'reactivate']);
    Storage::disk('adjuntos')->assertExists($adjunto->ruta);
});

it('permite consulta histórica pero bloquea toda mutación de integrantes dados de baja', function () {
    $adjunto = adjuntoLibreIntegrante($this->integrante);
    $this->integrante->update(['activo' => false]);
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.adjuntos.store', $this->integrante), [
        'archivo' => UploadedFile::fake()->create('Documento.pdf', 1, 'application/pdf'),
    ])->assertSessionHasErrors('archivo');
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.adjuntos.'.$accion, $parametros), ['observaciones' => 'Cambio'])
            ->assertSessionHasErrors('archivo');
    }
    $this->get(route('academico.integrantes.adjuntos.descargar', $parametros))->assertOk()->assertStreamedContent('Contenido privado');
    expect($this->integrante->adjuntos()->count())->toBe(1)->and($adjunto->fresh()->activo)->toBeTrue()
        ->and(Log::query()->where('entidad', 'adjuntos')->count())->toBe(0);
});

it('valida tipo extensión tamaño y observaciones antes de guardar', function (string $caso) {
    $datos = ['archivo' => UploadedFile::fake()->create('Archivo.pdf', 1, 'application/pdf')];
    match ($caso) {
        'tipo' => $datos['archivo'] = UploadedFile::fake()->create('Archivo.pdf', 1, 'text/html'),
        'extension' => $datos['archivo'] = UploadedFile::fake()->create('Archivo.exe', 1, 'application/pdf'),
        'tamano' => $datos['archivo'] = UploadedFile::fake()->create('Archivo.pdf', config('adjuntos.max_kb') + 1, 'application/pdf'),
        'observaciones' => $datos['observaciones'] = str_repeat('a', 2001),
    };
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.adjuntos.store', $this->integrante), $datos)
        ->assertSessionHasErrors($caso === 'observaciones' ? 'observaciones' : 'archivo');
    expect($this->integrante->adjuntos()->count())->toBe(0)->and(Storage::disk('adjuntos')->allFiles())->toBe([]);
})->with(['tipo', 'extension', 'tamano', 'observaciones']);

it('aplica las policies del integrante y del adjunto', function (string $modelo) {
    $adjunto = adjuntoLibreIntegrante($this->integrante);
    $this->actingAs(User::factory()->create());
    Gate::before(fn ($user, $ability, $arguments) => (($arguments[0] ?? null) instanceof $modelo || ($arguments[0] ?? null) === $modelo) ? false : null);
    $this->post(route('academico.integrantes.adjuntos.store', $this->integrante))->assertForbidden();
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    foreach (['descargar', 'miniatura'] as $accion) {
        $this->get(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertForbidden();
    }
    foreach (['update', 'desactivar', 'reactivar'] as $accion) {
        $this->patch(route('academico.integrantes.adjuntos.'.$accion, $parametros))->assertForbidden();
    }
})->with([[Integrante::class], [Adjunto::class]]);

it('revierte archivo y vínculo si falla la auditoría', function () {
    Event::listen('eloquent.creating: '.Log::class, function () {
        throw new RuntimeException('Fallo de auditoría');
    });
    try {
        $this->actingAs(User::factory()->create())->post(route('academico.integrantes.adjuntos.store', $this->integrante), [
            'archivo' => UploadedFile::fake()->create('Archivo.pdf', 1, 'application/pdf'),
        ])->assertServerError();
    } finally {
        Event::forget('eloquent.creating: '.Log::class);
    }
    $this->assertDatabaseCount('integrantes_adjuntos', 0);
    $this->assertDatabaseCount('adjuntos', 0);
    expect(Storage::disk('adjuntos')->allFiles())->toBe([]);
});

it('sirve miniaturas solo cuando el contenido real es una imagen permitida', function () {
    $adjunto = adjuntoLibreIntegrante($this->integrante);
    $parametros = ['integrante' => $this->integrante, 'adjunto' => $adjunto];
    $this->actingAs(User::factory()->create());
    $this->get(route('academico.integrantes.adjuntos.miniatura', $parametros))->assertNotFound();
    Storage::disk('adjuntos')->put($adjunto->ruta, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aP9sAAAAASUVORK5CYII='));
    $this->get(route('academico.integrantes.adjuntos.miniatura', $parametros))->assertOk()->assertHeader('Content-Type', 'image/png');
    Storage::disk('adjuntos')->delete($adjunto->ruta);
    $this->get(route('academico.integrantes.adjuntos.descargar', $parametros))->assertNotFound();
});
