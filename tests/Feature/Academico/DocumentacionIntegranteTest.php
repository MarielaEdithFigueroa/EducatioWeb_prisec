<?php

use App\Models\Adjunto;
use App\Models\Integrante;
use App\Models\IntegranteDocumentacion;
use App\Models\Log;
use App\Models\TipoDocumentacionIntegrante;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    app()->instance('archivosTemporalesPdf', collect());
    Storage::fake('adjuntos');
    $this->integrante = Integrante::factory()->create();
    $this->tipo = TipoDocumentacionIntegrante::factory()->create();
    $this->parametros = ['integrante' => $this->integrante, 'tipoDocumentacionIntegrante' => $this->tipo];
});

afterEach(function () {
    foreach (app('archivosTemporalesPdf') as $ruta) {
        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
});

function archivoRealLegajo(string $nombre, string $contenido): UploadedFile
{
    $ruta = tempnam(sys_get_temp_dir(), 'pdf-legajo-');
    file_put_contents($ruta, $contenido);
    app('archivosTemporalesPdf')->push($ruta);

    return new UploadedFile($ruta, $nombre, 'application/pdf', null, true);
}

function pdfLegajo(string $nombre = 'constancia.pdf'): UploadedFile
{
    return archivoRealLegajo($nombre, "%PDF-1.4\n1 0 obj\n<< /Type /Catalog >>\nendobj\n%%EOF");
}

it('requiere autenticación para adjuntar quitar y ver PDFs', function () {
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros))->assertRedirect(route('login'));
    $this->patch(route('academico.integrantes.documentacion.quitar', $this->parametros))->assertRedirect(route('login'));
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertRedirect(route('login'));
});

it('adjunta un PDF privado marca presentado y publica solo metadatos', function () {
    $usuario = User::factory()->create();
    $this->actingAs($usuario)->from(route('academico.integrantes.show', $this->integrante))
        ->post(route('academico.integrantes.documentacion.guardar', $this->parametros), [
            'archivo' => pdfLegajo(), 'adjunto_id' => null, 'ruta' => 'public/otro', 'disco' => 'public',
        ])->assertSessionHasNoErrors()->assertRedirect(route('academico.integrantes.show', $this->integrante));
    $registro = $this->integrante->documentacion()->sole();
    $pdf = $registro->adjuntos()->sole();
    expect($registro->presentado)->toBeTrue()->and($pdf->activo)->toBeTrue()
        ->and($pdf->disco)->toBe('adjuntos')->and($pdf->usuario_id)->toBe($usuario->id);
    Storage::disk('adjuntos')->assertExists($pdf->ruta);
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Cache-Control', 'no-store, private')->assertStreamedContent(pdfLegajo()->getContent());
    $this->get(route('academico.integrantes.show', $this->integrante))->assertInertia(fn (Assert $page) => $page
        ->where('documentacion.0.adjunto.id', $pdf->id)->where('documentacion.0.presentado', true)
        ->missing('documentacion.0.adjunto.ruta')->missing('documentacion.0.adjunto.disco')->has('configPdf'));
    expect(Log::query()->where('entidad', 'adjuntos')->sole()->nuevo['vinculo'])
        ->toBe(['entidad' => 'integrantes_documentacion', 'entidad_id' => $registro->id]);
});

it('reemplaza y quita con baja lógica conservando el estado presentado y los archivos', function () {
    $this->actingAs(User::factory()->create());
    $url = route('academico.integrantes.documentacion.guardar', $this->parametros);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $primero = Adjunto::query()->sole();
    $this->post($url, ['archivo' => pdfLegajo('nuevo.pdf'), 'adjunto_id' => $primero->id])->assertSessionHasNoErrors();
    $nuevo = Adjunto::query()->where('activo', true)->sole();
    expect($primero->fresh()->activo)->toBeFalse()->and($nuevo->nombre_original)->toBe('nuevo.pdf');
    $this->patch(route('academico.integrantes.documentacion.quitar', $this->parametros), ['adjunto_id' => $nuevo->id])->assertSessionHasNoErrors();
    expect($nuevo->fresh()->activo)->toBeFalse()->and($this->integrante->documentacion()->sole()->presentado)->toBeTrue();
    Storage::disk('adjuntos')->assertExists([$primero->ruta, $nuevo->ruta]);
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertNotFound();
    expect(Log::query()->where('entidad', 'adjuntos')->where('accion', 'deactivate')->count())->toBe(2);
});

it('rechaza reemplazos y bajas desde una ficha desactualizada', function () {
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $pdf = Adjunto::query()->sole();
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasErrors('archivo');
    $this->patch(route('academico.integrantes.documentacion.quitar', $this->parametros), ['adjunto_id' => $pdf->id + 1])->assertSessionHasErrors('archivo');
    expect($pdf->fresh()->activo)->toBeTrue()->and(Adjunto::query()->count())->toBe(1);
});

it('rechaza archivos inválidos sin cambiar el checklist', function (string $caso) {
    $archivo = match ($caso) {
        'html' => archivoRealLegajo('falso.pdf', '<html><script>alert(1)</script></html>'),
        'extension' => pdfLegajo('archivo.txt'),
        'grande' => UploadedFile::fake()->create('archivo.pdf', config('adjuntos.max_kb') + 1, 'application/pdf'),
    };
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => $archivo, 'adjunto_id' => null])
        ->assertSessionHasErrors('archivo');
    expect(Adjunto::query()->count())->toBe(0)->and($this->integrante->documentacion()->count())->toBe(0);
})->with(['html', 'extension', 'grande']);

it('impide cambiar documentación de integrantes inactivos pero permite consultar el PDF histórico', function () {
    $this->actingAs(User::factory()->create());
    $url = route('academico.integrantes.documentacion.guardar', $this->parametros);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $pdf = Adjunto::query()->sole();
    $this->integrante->update(['activo' => false]);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => $pdf->id])->assertSessionHasErrors('archivo');
    $this->patch(route('academico.integrantes.documentacion.quitar', $this->parametros), ['adjunto_id' => $pdf->id])->assertSessionHasErrors('archivo');
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertOk();
});

it('rechaza tipos inactivos nuevos y conserva asociaciones históricas', function () {
    $this->actingAs(User::factory()->create());
    $this->tipo->update(['activo' => false]);
    $url = route('academico.integrantes.documentacion.guardar', $this->parametros);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasErrors('archivo');
    $this->integrante->documentacion()->create(['tipo_documentacion_integrante_id' => $this->tipo->id]);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertOk();
});

it('resuelve el PDF únicamente dentro del integrante y tipo pedidos', function () {
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $otro = Integrante::factory()->create();
    $tipo = TipoDocumentacionIntegrante::factory()->create();
    $this->get(route('academico.integrantes.documentacion.ver', [...$this->parametros, 'integrante' => $otro]))->assertNotFound();
    $this->get(route('academico.integrantes.documentacion.ver', [...$this->parametros, 'tipoDocumentacionIntegrante' => $tipo]))->assertNotFound();
    $pdf = Adjunto::query()->sole();
    Storage::disk('adjuntos')->put($pdf->ruta, '<html>Alterado</html>');
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertNotFound();
    Storage::disk('adjuntos')->delete($pdf->ruta);
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertNotFound();
});

it('aplica policies a la carga lectura y baja de PDFs', function () {
    $this->actingAs(User::factory()->create());
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $pdf = Adjunto::query()->sole();
    Gate::before(fn () => false);
    $this->get(route('academico.integrantes.documentacion.ver', $this->parametros))->assertForbidden();
    $this->post(route('academico.integrantes.documentacion.guardar', $this->parametros), ['archivo' => pdfLegajo(), 'adjunto_id' => $pdf->id])->assertForbidden();
    $this->patch(route('academico.integrantes.documentacion.quitar', $this->parametros), ['adjunto_id' => $pdf->id])->assertForbidden();
});

it('guarda PDFs junto con el alta y permite quitarlos durante la edición', function () {
    $this->actingAs(User::factory()->create());
    $datos = ['legajo' => 'PDF-100', 'apellido' => 'Pérez', 'nombre' => 'Ana'];
    $fila = ['tipo_documentacion_integrante_id' => $this->tipo->id, 'presentado' => false, 'archivo' => pdfLegajo(), 'adjunto_id' => null];
    $this->post(route('academico.integrantes.store'), [...$datos, 'documentacion' => [$fila]])->assertSessionHasNoErrors();
    $integrante = Integrante::query()->where('legajo', 'PDF-100')->sole();
    $pdf = $integrante->documentacion()->sole()->adjuntos()->sole();
    expect($integrante->documentacion()->sole()->presentado)->toBeTrue();
    $this->patch(route('academico.integrantes.update', $integrante), [...$datos, 'documentacion' => [[
        'tipo_documentacion_integrante_id' => $this->tipo->id, 'presentado' => true, 'eliminar_archivo' => true, 'adjunto_id' => $pdf->id,
    ]]])->assertSessionHasNoErrors();
    expect($pdf->fresh()->activo)->toBeFalse()->and($integrante->documentacion()->sole()->presentado)->toBeTrue();
});

it('valida PDFs y operaciones contradictorias también en el formulario del legajo', function (bool $contradictorio) {
    $this->actingAs(User::factory()->create())->post(route('academico.integrantes.store'), [
        'legajo' => 'PDF-100', 'apellido' => 'Pérez', 'nombre' => 'Ana',
        'documentacion' => [[
            'tipo_documentacion_integrante_id' => $this->tipo->id, 'presentado' => true,
            'archivo' => $contradictorio ? pdfLegajo() : archivoRealLegajo('falso.pdf', 'texto plano'),
            'eliminar_archivo' => $contradictorio,
        ]],
    ])->assertSessionHasErrors('documentacion.0.archivo');
    expect(Adjunto::query()->count())->toBe(0);
})->with([true, false]);

it('revierte el reemplazo si falla la auditoría y conserva el archivo anterior', function () {
    $this->actingAs(User::factory()->create());
    $url = route('academico.integrantes.documentacion.guardar', $this->parametros);
    $this->post($url, ['archivo' => pdfLegajo(), 'adjunto_id' => null])->assertSessionHasNoErrors();
    $anterior = Adjunto::query()->sole();
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'adjuntos' && $log->accion === 'create') {
            throw new RuntimeException('Fallo de auditoría');
        }
    });
    $this->withoutExceptionHandling();
    expect(fn () => $this->post($url, ['archivo' => pdfLegajo('nuevo.pdf'), 'adjunto_id' => $anterior->id]))->toThrow(RuntimeException::class, 'Fallo de auditoría');
    expect(Adjunto::query()->count())->toBe(1)->and($anterior->fresh()->activo)->toBeTrue()
        ->and(Storage::disk('adjuntos')->allFiles())->toBe([$anterior->ruta]);
});

it('limpia todos los PDFs nuevos cuando falla una fila posterior del mismo legajo', function () {
    $otroTipo = TipoDocumentacionIntegrante::factory()->create();
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'adjuntos' && ($log->nuevo['nombre_original'] ?? '') === 'falla.pdf') {
            throw new RuntimeException('Fallo en segundo PDF');
        }
    });
    $this->actingAs(User::factory()->create())->withoutExceptionHandling();
    expect(fn () => $this->post(route('academico.integrantes.store'), [
        'legajo' => 'PDF-100', 'apellido' => 'Pérez', 'nombre' => 'Ana',
        'documentacion' => [
            ['tipo_documentacion_integrante_id' => $this->tipo->id, 'presentado' => false, 'archivo' => pdfLegajo()],
            ['tipo_documentacion_integrante_id' => $otroTipo->id, 'presentado' => false, 'archivo' => pdfLegajo('falla.pdf')],
        ],
    ]))->toThrow(RuntimeException::class, 'Fallo en segundo PDF');
    expect(Adjunto::query()->count())->toBe(0)->and(IntegranteDocumentacion::query()->count())->toBe(0)
        ->and(Integrante::query()->where('legajo', 'PDF-100')->exists())->toBeFalse()
        ->and(Storage::disk('adjuntos')->allFiles())->toBe([]);
});
