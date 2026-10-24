<?php

use App\Models\FuncionEquipo;
use App\Models\Integrante;
use App\Models\IntegranteFuncion;
use App\Models\Log;
use App\Models\User;
use Database\Seeders\FuncionEquipoSeeder;
use Illuminate\Support\Facades\Log as Logger;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(FuncionEquipoSeeder::class);
    $this->actingAs(User::factory()->create());
});

function datosPersonalFunciones(array $extra = []): array
{
    return [...['legajo' => 'PF-1', 'apellido' => 'Pérez', 'nombre' => 'Ana'], ...$extra];
}

it('publica el catálogo ordenado y lo siembra sin duplicados', function () {
    $ids = FuncionEquipo::query()->orderBy('orden')->pluck('id')->all();
    $this->seed(FuncionEquipoSeeder::class);
    expect(FuncionEquipo::query()->count())->toBe(10)
        ->and(FuncionEquipo::query()->orderBy('orden')->pluck('id')->all())->toBe($ids);
    $this->get(route('academico.integrantes.create'))->assertInertia(fn (Assert $page) => $page
        ->has('catalogos.funciones_equipo', 10)
        ->where('catalogos.funciones_equipo.0.codigo', 'docencia')
        ->where('catalogos.funciones_equipo.9.codigo', 'mantenimiento'));
});

it('guarda varias funciones y las muestra sin duplicar personas al filtrar', function () {
    $ids = FuncionEquipo::query()->orderBy('orden')->limit(2)->pluck('id')->all();
    $this->post(route('academico.integrantes.store'), datosPersonalFunciones(['funciones_ids' => $ids]))->assertSessionHasNoErrors();
    $persona = Integrante::query()->sole();
    expect($persona->funciones->pluck('id')->all())->toBe($ids)
        ->and(Log::query()->where('entidad', 'integrantes_funciones')->where('accion', 'create')->count())->toBe(2);
    Integrante::factory()->create();
    foreach ($ids as $id) {
        $this->get(route('academico.integrantes.index', ['funcion_id' => $id]))->assertInertia(fn (Assert $page) => $page
            ->has('integrantes', 1)->has('integrantes.0.funciones', 2)->where('integrantes.0.id', $persona->id));
    }
    foreach (['show', 'edit', 'imprimir'] as $pantalla) {
        $this->get(route('academico.integrantes.'.$pantalla, $persona))->assertInertia(fn (Assert $page) => $page
            ->has('integrante.funciones', 2)->where('integrante.funciones.0.id', $ids[0]));
    }
});

it('destilda y vuelve a tildar conservando el vínculo y auditando solo cambios', function () {
    $ids = FuncionEquipo::query()->orderBy('orden')->limit(2)->pluck('id')->all();
    $this->post(route('academico.integrantes.store'), datosPersonalFunciones(['funciones_ids' => $ids]))->assertSessionHasNoErrors();
    $persona = Integrante::query()->sole();
    $vinculo = $persona->asignacionesFunciones()->where('funcion_equipo_id', $ids[1])->sole();
    $this->patch(route('academico.integrantes.update', $persona), datosPersonalFunciones(['funciones_ids' => [$ids[0]]]))->assertSessionHasNoErrors();
    expect($vinculo->fresh()->activo)->toBeFalse()->and($persona->fresh()->funciones)->toHaveCount(1);
    $this->patch(route('academico.integrantes.update', $persona), datosPersonalFunciones(['funciones_ids' => $ids]))->assertSessionHasNoErrors();
    expect($vinculo->fresh()->activo)->toBeTrue()->and(IntegranteFuncion::query()->count())->toBe(2);
    $antes = Log::query()->count();
    $this->patch(route('academico.integrantes.update', $persona), datosPersonalFunciones(['funciones_ids' => $ids]))->assertSessionHasNoErrors();
    expect(Log::query()->count())->toBe($antes)
        ->and(Log::query()->where('entidad', 'integrantes_funciones')->where('entidad_id', $vinculo->id)->orderBy('id')->pluck('accion')->all())
        ->toBe(['create', 'deactivate', 'reactivate']);
});

it('distingue no enviar funciones de desmarcar todas incluso con multipart', function () {
    $id = FuncionEquipo::query()->firstOrFail()->id;
    $this->post(route('academico.integrantes.store'), datosPersonalFunciones(['funciones_ids' => [$id]]))->assertSessionHasNoErrors();
    $persona = Integrante::query()->sole();
    $this->patch(route('academico.integrantes.update', $persona), datosPersonalFunciones())->assertSessionHasNoErrors();
    expect($persona->fresh()->funciones)->toHaveCount(1);
    $this->post(route('academico.integrantes.update', $persona), datosPersonalFunciones(['_method' => 'patch', 'funciones_ids' => '']))->assertSessionHasNoErrors();
    expect($persona->fresh()->funciones)->toHaveCount(0)->and($persona->asignacionesFunciones()->sole()->activo)->toBeFalse();
});

it('rechaza funciones inexistentes repetidas o mal formadas sin escribir datos', function (mixed $valor, string $campo) {
    $this->post(route('academico.integrantes.store'), datosPersonalFunciones(['funciones_ids' => $valor]))->assertSessionHasErrors($campo);
    expect(Integrante::query()->count())->toBe(0)->and(IntegranteFuncion::query()->count())->toBe(0);
})->with([
    'inexistente' => [[999999], 'funciones_ids.0'],
    'repetida' => [[1, 1], 'funciones_ids.0'],
    'texto' => [['docencia'], 'funciones_ids.0'],
    'objeto anidado' => [[['id' => 1]], 'funciones_ids.0'],
    'escalar' => [1, 'funciones_ids'],
]);

it('conserva las funciones al dar de baja y reincorporar una persona', function () {
    $id = FuncionEquipo::query()->firstOrFail()->id;
    $this->post(route('academico.integrantes.store'), datosPersonalFunciones(['funciones_ids' => [$id]]))->assertSessionHasNoErrors();
    $persona = Integrante::query()->sole();
    $this->patch(route('academico.integrantes.desactivar', $persona), ['fecha_baja' => now()->toDateString(), 'motivo_baja' => 'Fin de contrato'])->assertSessionHasNoErrors();
    expect($persona->fresh()->funciones)->toHaveCount(1);
    $this->patch(route('academico.integrantes.reactivar', $persona))->assertSessionHasNoErrors();
    expect($persona->fresh()->funciones)->toHaveCount(1)->and(IntegranteFuncion::query()->count())->toBe(1);
});

it('revierte persona funciones y auditoría si falla la auditoría de un vínculo', function () {
    $this->withoutExceptionHandling();
    Logger::setDefaultDriver('null');
    Log::creating(function (Log $log): void {
        if ($log->entidad === 'integrantes_funciones') {
            throw new RuntimeException('Fallo en función');
        }
    });
    expect(fn () => $this->post(route('academico.integrantes.store'), datosPersonalFunciones([
        'funciones_ids' => [FuncionEquipo::query()->firstOrFail()->id],
    ])))->toThrow(RuntimeException::class, 'Fallo en función');
    expect(Integrante::query()->count())->toBe(0)->and(IntegranteFuncion::query()->count())->toBe(0)->and(Log::query()->count())->toBe(0);
});
