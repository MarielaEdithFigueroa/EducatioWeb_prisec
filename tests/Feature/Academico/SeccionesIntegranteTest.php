<?php

use App\Models\Integrante;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('abre consulta edición e impresión en la sección solicitada', function (string $seccion) {
    $integrante = Integrante::factory()->create();
    $this->actingAs(User::factory()->create());
    foreach (['show', 'edit', 'imprimir'] as $accion) {
        $this->get(route('academico.integrantes.'.$accion, ['integrante' => $integrante, 'seccion' => $seccion]))
            ->assertInertia(fn (Assert $page) => $page->where('seccion', $seccion));
    }
})->with(['personal', 'documentacion', 'adjuntos', 'otros']);

it('conserva sección y filtros después de guardar o guardar y continuar', function (bool $continuar, string $seccion) {
    $integrante = Integrante::factory()->create();
    $filtros = ['buscar' => 'Pérez', 'estado' => 'activos', 'funcion_id' => ''];
    $this->actingAs(User::factory()->create())->patch(route('academico.integrantes.update', $integrante), [
        'legajo' => $integrante->legajo, 'apellido' => 'Pérez', 'nombre' => 'Ana',
        'seccion' => $seccion, 'continuar' => $continuar, 'filtros' => $filtros,
    ])->assertSessionHasNoErrors()->assertRedirect(route('academico.integrantes.'.($continuar ? 'edit' : 'show'), [
        'integrante' => $integrante, 'seccion' => $seccion, 'filtros' => $filtros,
    ]));
    expect($integrante->fresh()->apellido)->toBe('Pérez');
})->with([true, false])->with(['otros', 'adjuntos']);

it('mantiene la sección al redirigir la edición de un integrante dado de baja', function () {
    $integrante = Integrante::factory()->create(['activo' => false]);
    $this->actingAs(User::factory()->create())->get(route('academico.integrantes.edit', ['integrante' => $integrante, 'seccion' => 'documentacion']))
        ->assertRedirect(route('academico.integrantes.show', [
            'integrante' => $integrante,
            'filtros' => ['buscar' => '', 'estado' => 'activos', 'funcion_id' => ''],
            'seccion' => 'documentacion',
        ]));
});

it('usa datos personales por defecto y rechaza secciones desconocidas', function () {
    $integrante = Integrante::factory()->create();
    $this->actingAs(User::factory()->create())->get(route('academico.integrantes.show', $integrante))
        ->assertInertia(fn (Assert $page) => $page->where('seccion', 'personal'));
    $this->get(route('academico.integrantes.edit', ['integrante' => $integrante, 'seccion' => 'inexistente']))->assertSessionHasErrors('seccion');
    $this->patch(route('academico.integrantes.update', $integrante), [
        'legajo' => $integrante->legajo, 'apellido' => 'Cambio', 'nombre' => 'Ana', 'seccion' => 'inexistente',
    ])->assertSessionHasErrors('seccion');
    expect($integrante->fresh()->apellido)->toBe($integrante->apellido);
});
