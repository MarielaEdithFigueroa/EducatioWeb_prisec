<?php

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Log;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;

it('requiere autenticación en el panel y en la consulta de alumnos', function () {
    $grupo = Grupo::factory()->create();
    $this->get(route('academico.grupos.panel'))->assertRedirect(route('login'));
    $this->getJson(route('academico.grupos.alumnos.index', $grupo))->assertRedirect(route('login'));
});

it('muestra grupos de distintos años incluidos inactivos para filtrarlos sin recargar', function () {
    $primero = Grupo::factory()->create(['ciclo_lectivo' => 2025]);
    $segundo = Grupo::factory()->create(['ciclo_lectivo' => 2026, 'activo' => false]);
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.panel', ['ciclo_lectivo' => 2025]))
        ->assertInertia(fn (Assert $page) => $page->component('academico/grupos/panel')
            ->where('cicloInicial', 2025)->has('grupos', 2)
            ->where('grupos.0.id', $segundo->id)->where('grupos.0.activo', false)
            ->where('grupos.0.ciclo_lectivo', 2026)->where('grupos.0.descripcion', $segundo->descripcion)
            ->where('grupos.1.id', $primero->id)->where('grupos.1.ciclo_lectivo', 2025)
            ->missing('grupos.0.alumnos')->missing('alumnos'));
});

it('conserva un año solicitado sin grupos y usa el actual solamente si no se indica', function () {
    Grupo::factory()->create(['ciclo_lectivo' => 2025]);
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.panel', ['ciclo_lectivo' => 2040]))
        ->assertInertia(fn (Assert $page) => $page->where('cicloInicial', 2040));
    $this->get(route('academico.grupos.panel'))->assertInertia(fn (Assert $page) => $page->where('cicloInicial', now()->year));
});

it('rechaza años inválidos en el panel', function (mixed $ciclo) {
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.panel', ['ciclo_lectivo' => $ciclo]))
        ->assertSessionHasErrors('ciclo_lectivo');
})->with(['texto', 1899, 10000, '2026.5']);

it('consulta únicamente alumnos actuales del grupo con campos mínimos y sin escribir logs', function () {
    $usuario = User::factory()->create();
    $origen = Grupo::factory()->create();
    $destino = Grupo::factory()->create();
    $alumno = Alumno::query()->create(['legajo' => 'PANEL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana', 'observaciones' => 'Dato privado']);
    $this->actingAs($usuario)->patch(route('academico.alumnos.grupo.update', $alumno), [
        'grupo_id' => $origen->id, 'version_historial' => '0', 'motivo' => 'Ingreso',
    ])->assertSessionHasNoErrors();
    $this->patch(route('academico.alumnos.grupo.update', $alumno), [
        'grupo_id' => $destino->id, 'version_historial' => $alumno->asignacionActual()->sole()->id.':vigente', 'motivo' => 'Traslado',
    ])->assertSessionHasNoErrors();
    $logs = Log::query()->count();

    $this->getJson(route('academico.grupos.alumnos.index', $origen))->assertOk()
        ->assertExactJson(['grupo_id' => $origen->id, 'alumnos' => []]);
    $respuesta = $this->getJson(route('academico.grupos.alumnos.index', $destino))->assertOk()
        ->assertExactJson(['grupo_id' => $destino->id, 'alumnos' => [
            ['id' => $alumno->id, 'legajo' => 'PANEL-1', 'apellido' => 'Pérez', 'nombre' => 'Ana'],
        ]]);
    expect($respuesta->headers->get('Cache-Control'))->toContain('no-store')
        ->and(Log::query()->count())->toBe($logs);
});

it('permite consultar un grupo inactivo y devuelve 404 si no existe', function () {
    $grupo = Grupo::factory()->create(['activo' => false]);
    $this->actingAs(User::factory()->create())->getJson(route('academico.grupos.alumnos.index', $grupo))
        ->assertOk()->assertExactJson(['grupo_id' => $grupo->id, 'alumnos' => []]);
    $this->getJson(route('academico.grupos.alumnos.index', 999999))->assertNotFound();
});

it('respeta la policy de consulta del grupo', function () {
    $grupo = Grupo::factory()->create();
    Gate::before(fn (User $user, string $habilidad): ?bool => $habilidad === 'view' ? false : null);
    $this->actingAs(User::factory()->create())->getJson(route('academico.grupos.alumnos.index', $grupo))->assertForbidden();
});

it('respeta la policy de consulta de alumnos en ambas entradas', function () {
    $grupo = Grupo::factory()->create();
    Gate::before(fn (User $user, string $habilidad, array $argumentos): ?bool => $habilidad === 'viewAny' && ($argumentos[0] ?? null) === Alumno::class ? false : null);
    $this->actingAs(User::factory()->create())->get(route('academico.grupos.panel'))->assertForbidden();
    $this->getJson(route('academico.grupos.alumnos.index', $grupo))->assertForbidden();
});
