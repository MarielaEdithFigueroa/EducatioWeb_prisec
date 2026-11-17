<?php

use App\Actions\Academico\GestionarGrupoAlumno;
use App\Models\Alumno;
use App\Models\AlumnoGrupo;
use App\Models\Grupo;
use App\Models\Log;
use App\Models\TipoDocumento;
use Database\Seeders\AlumnosGruposSeeder;
use Database\Seeders\CursosSeeder;
use Database\Seeders\DivisionesSeeder;
use Database\Seeders\GruposSeeder;
use Database\Seeders\NacionalidadesSeeder;
use Database\Seeders\NivelesSeeder;
use Database\Seeders\ProvinciasYCiudadesSeeder;
use Database\Seeders\TiposDocumentoSeeder;
use Database\Seeders\TurnosSeeder;

it('asigna los ejemplos activos al ciclo actual con historial y auditoría de consola', function () {
    $this->travelTo(now()->setDate(2026, 11, 17)->setTime(10, 30));
    $this->seed();

    $alumnos = Alumno::query()->whereNotNull('grupo_id')->with(['grupo', 'asignacionActual'])->get();
    expect($alumnos)->toHaveCount(210)
        ->and(AlumnoGrupo::query()->count())->toBe(210)
        ->and(Alumno::query()->count())->toBe(216)
        ->and(Alumno::query()->whereLike('legajo', 'SEC-%')->whereNotNull('grupo_id')->count())->toBe(0)
        ->and(Alumno::query()->where('activo', false)->sole()->grupo_id)->toBeNull();

    $grupos = Grupo::query()->withCount('alumnos')->get();
    expect($grupos)->toHaveCount(14)
        ->and($grupos->min('alumnos_count'))->toBe(12)
        ->and($grupos->max('alumnos_count'))->toBe(18);
    foreach ($grupos as $grupo) {
        expect($grupo->alumnos_count)->toBeBetween(12, 18);
    }

    foreach ($alumnos as $alumno) {
        expect($alumno->grupo)->not->toBeNull()
            ->and($alumno->grupo->activo)->toBeTrue()
            ->and($alumno->grupo->ciclo_lectivo)->toBe(2026)
            ->and($alumno->asignacionActual->grupo_id)->toBe($alumno->grupo_id)
            ->and($alumno->asignacionActual->fecha_desde->equalTo(now()))->toBeTrue()
            ->and($alumno->asignacionActual->motivo_ingreso)->toBe('Asignación inicial de datos de ejemplo');
    }

    foreach (Alumno::query()->whereLike('legajo', 'DEMO-PRI-%')->with('grupo.curso')->get() as $alumno) {
        expect($alumno->cohorte_nacimiento)->toBe(2026 - $alumno->grupo->curso->orden - 5);
    }

    $logs = Log::query()->get();
    expect($logs)->toHaveCount(624)
        ->and($logs->where('entidad', 'alumnos_grupos')->where('accion', 'create'))->toHaveCount(210)
        ->and($logs->where('entidad', 'alumnos')->where('accion', 'update'))->toHaveCount(210)
        ->and($logs->where('entidad', 'alumnos')->where('accion', 'create'))->toHaveCount(204);

    foreach ($logs as $log) {
        expect($log->origen)->toBe('consola')
            ->and($log->usuario_id)->toBeNull()
            ->and($log->login)->toBeNull()
            ->and($log->session_id)->toBeNull()
            ->and($log->ip)->toBeNull()
            ->and($log->user_agent)->toBeNull()
            ->and($log->created_at->equalTo(now()))->toBeTrue();
    }

    $historial = AlumnoGrupo::query()->orderBy('id')->get()->toArray();
    $datosAlumnos = Alumno::query()->orderBy('id')->get()->toArray();
    $this->seed();
    $this->seed(AlumnosGruposSeeder::class);

    expect(AlumnoGrupo::query()->orderBy('id')->get()->toArray())->toBe($historial)
        ->and(Alumno::query()->orderBy('id')->get()->toArray())->toBe($datosAlumnos)
        ->and(Log::query()->count())->toBe(624);
});

it('conserva traslados cierres y reactivaciones al repetir el seed general', function () {
    $this->seed();
    $gestionar = app(GestionarGrupoAlumno::class);
    $trasladado = Alumno::query()->where('legajo', 'PRI-2026-0001')->sole();
    $cerrado = Alumno::query()->where('legajo', 'PRI-2026-0002')->sole();
    $reactivada = Alumno::query()->where('legajo', 'SEC-2026-0010')->sole();
    $destino = Grupo::query()->where('activo', true)->where('ciclo_lectivo', now()->year)
        ->whereKeyNot($trasladado->grupo_id)->firstOrFail();

    $gestionar->cambiar($trasladado, $destino->id, AlumnoGrupo::version($trasladado->asignacionActual), 'Traslado posterior');
    $gestionar->cambiar($cerrado, null, AlumnoGrupo::version($cerrado->asignacionActual), 'Cierre posterior');
    $reactivada->update(['activo' => true, 'fecha_baja' => null, 'motivo_baja_id' => null]);
    $gestionar->cambiar($reactivada, $destino->id, '0', 'Reingreso posterior');
    $generado = Alumno::query()->whereLike('legajo', 'DEMO-PRI-%')->firstOrFail();
    $gestionar->cerrarPorBaja($generado);
    $generado->update(['activo' => false, 'fecha_baja' => now()->toDateString(), 'nombre' => 'Nombre corregido']);
    $historial = AlumnoGrupo::query()->orderBy('id')->get()->toArray();
    $cantidadLogs = Log::query()->count();
    $cantidadAlumnos = Alumno::query()->count();

    $this->seed();

    expect($trasladado->fresh()->grupo_id)->toBe($destino->id)
        ->and($cerrado->fresh()->grupo_id)->toBeNull()
        ->and($reactivada->fresh()->activo)->toBeTrue()
        ->and($reactivada->fresh()->fecha_baja)->toBeNull()
        ->and($reactivada->fresh()->motivo_baja_id)->toBeNull()
        ->and($reactivada->fresh()->grupo_id)->toBe($destino->id)
        ->and($generado->fresh()->activo)->toBeFalse()
        ->and($generado->fresh()->grupo_id)->toBeNull()
        ->and($generado->fresh()->nombre)->toBe('Nombre corregido')
        ->and(Alumno::query()->count())->toBe($cantidadAlumnos)
        ->and(AlumnoGrupo::query()->orderBy('id')->get()->toArray())->toBe($historial)
        ->and(Log::query()->count())->toBe($cantidadLogs);
});

it('no genera planteles fuera de desarrollo y tests', function () {
    $this->seed([TiposDocumentoSeeder::class, NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class, GruposSeeder::class]);
    $entorno = app()->environment();
    app()->instance('env', 'production');

    try {
        app(AlumnosGruposSeeder::class)->run(app(GestionarGrupoAlumno::class));
        expect(Alumno::query()->count())->toBe(0)
            ->and(AlumnoGrupo::query()->count())->toBe(0)
            ->and(Log::query()->count())->toBe(0);
    } finally {
        app()->instance('env', $entorno);
    }
});

it('reserva los lugares de ejemplos originales si el plantel se ejecuta primero', function () {
    $this->seed([TiposDocumentoSeeder::class, NacionalidadesSeeder::class, ProvinciasYCiudadesSeeder::class, NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class, GruposSeeder::class, AlumnosGruposSeeder::class]);
    expect(Alumno::query()->count())->toBe(204);

    $this->seed();

    expect(Alumno::query()->count())->toBe(216)
        ->and(AlumnoGrupo::query()->count())->toBe(210)
        ->and(Alumno::query()->whereNull('ciudad_id')->count())->toBe(0)
        ->and(Alumno::query()->whereNull('nacionalidad_id')->count())->toBe(0);
    foreach (Grupo::query()->withCount('alumnos')->get() as $grupo) {
        expect($grupo->alumnos_count)->toBeBetween(12, 18);
    }
});

it('omite destinos inactivos alumnos inactivos y alumnos ajenos a los ejemplos', function () {
    $this->seed([TiposDocumentoSeeder::class, NacionalidadesSeeder::class, ProvinciasYCiudadesSeeder::class, NivelesSeeder::class, CursosSeeder::class, TurnosSeeder::class, DivisionesSeeder::class, GruposSeeder::class]);
    $grupo = Grupo::query()->where('ciclo_lectivo', now()->year)
        ->whereHas('nivel', fn ($query) => $query->where('codigo', 'PRIMARIA'))
        ->whereHas('curso', fn ($query) => $query->where('descripcion', '5.º grado'))
        ->whereHas('division', fn ($query) => $query->where('descripcion', 'A'))->sole();
    $grupo->update(['activo' => false]);
    foreach ([['PRI-2026-0001', true], ['PRI-2026-0003', false], ['AJENO-1', true]] as [$legajo, $activo]) {
        Alumno::query()->create(['legajo' => $legajo, 'activo' => $activo, 'apellido' => 'Ejemplo', 'nombre' => 'Prueba']);
    }

    $this->seed(AlumnosGruposSeeder::class);

    expect(AlumnoGrupo::query()->where('grupo_id', $grupo->id)->count())->toBe(0)
        ->and(Alumno::query()->whereIn('legajo', ['PRI-2026-0001', 'PRI-2026-0003', 'AJENO-1'])->whereNotNull('grupo_id')->count())->toBe(0)
        ->and(Alumno::query()->whereLike('legajo', 'DEMO-PRI-%-5A-%')->count())->toBe(0)
        ->and($grupo->fresh()->activo)->toBeFalse();
});

it('genera identidades variadas con DNI únicos y domicilios coherentes', function () {
    $this->travelTo(now()->setDate(2026, 11, 17));
    $this->seed();
    $alumnos = Alumno::query()->with(['tipoDocumento', 'ciudad.provincia'])->orderBy('id')->get();

    expect($alumnos->map(fn (Alumno $alumno): string => $alumno->apellido.'|'.$alumno->nombre)->unique())->toHaveCount(216)
        ->and($alumnos->pluck('numero_documento')->unique())->toHaveCount(216);
    foreach ($alumnos as $alumno) {
        expect($alumno->tipoDocumento->nombre)->toBe('DNI')
            ->and($alumno->numero_documento)->toMatch('/^\d{8}$/')
            ->and($alumno->domicilio)->not->toBeEmpty()
            ->and($alumno->ciudad)->not->toBeNull();
    }

    $generados = $alumnos->filter(fn (Alumno $alumno): bool => str_starts_with($alumno->legajo, 'DEMO-PRI-'));
    expect($generados->pluck('nombre')->unique()->count())->toBeGreaterThanOrEqual(40)
        ->and($generados->pluck('apellido')->unique()->count())->toBeGreaterThanOrEqual(40)
        ->and($generados->pluck('ciudad_id')->unique())->toHaveCount(4);
    foreach ($generados as $alumno) {
        expect($alumno->cpa)->toBe(match ($alumno->ciudad->nombre) {
            'Allen' => 'R8328', 'Cipolletti' => 'R8324', 'Neuquén' => 'Q8300', 'Plottier' => 'Q8316',
        });
    }

    $documentos = $alumnos->pluck('numero_documento', 'legajo')->all();
    $this->travel(1)->years();
    $this->seed();

    expect(Alumno::query()->count())->toBe(420)
        ->and(Alumno::query()->distinct()->count('numero_documento'))->toBe(420)
        ->and(Alumno::query()->whereIn('legajo', array_keys($documentos))->orderBy('id')->pluck('numero_documento', 'legajo')->all())->toBe($documentos);
});

it('completa el seed antiguo sin perder bajas ni historial y audita una sola actualización', function () {
    $this->travelTo(now()->setDate(2026, 11, 17));
    $this->seed();
    $alumno = Alumno::query()->where('legajo', 'DEMO-PRI-2026-1A-02')->sole();
    app(GestionarGrupoAlumno::class)->cerrarPorBaja($alumno);
    $alumno->update([
        'activo' => false, 'fecha_baja' => now()->toDateString(),
        'nombre' => 'Tomás', 'apellido' => 'Bustos',
        'tipo_documento_id' => null, 'numero_documento' => null,
        'domicilio' => null, 'ciudad_id' => null, 'cpa' => null,
        'sexo_registral' => null, 'genero' => null,
    ]);
    $historial = AlumnoGrupo::query()->orderBy('id')->get()->toArray();
    $cantidadLogs = Log::query()->count();

    $this->seed(AlumnosGruposSeeder::class);
    $alumno->refresh();

    expect($alumno->nombre)->toBe('Tomás')
        ->and($alumno->apellido)->toBe('Hernández')
        ->and($alumno->numero_documento)->toMatch('/^\d{8}$/')
        ->and($alumno->tipoDocumento->nombre)->toBe('DNI')
        ->and($alumno->domicilio)->not->toBeNull()
        ->and($alumno->activo)->toBeFalse()
        ->and($alumno->grupo_id)->toBeNull()
        ->and($alumno->fecha_baja->toDateString())->toBe(now()->toDateString())
        ->and(AlumnoGrupo::query()->orderBy('id')->get()->toArray())->toBe($historial)
        ->and(Log::query()->count())->toBe($cantidadLogs + 1);
    $log = Log::query()->latest('id')->firstOrFail();
    expect($log->entidad)->toBe('alumnos')
        ->and($log->entidad_id)->toBe($alumno->id)
        ->and($log->accion)->toBe('update')
        ->and($log->anterior['numero_documento'])->toBeNull()
        ->and($log->nuevo['numero_documento'])->toBe($alumno->numero_documento);

    $this->seed(AlumnosGruposSeeder::class);
    expect(Log::query()->count())->toBe($cantidadLogs + 1)
        ->and(Alumno::query()->count())->toBe(216);
});

it('respeta nombres domicilios y documentos corregidos al enriquecer ejemplos antiguos', function () {
    $this->seed();
    $alumnos = Alumno::query()->whereLike('legajo', 'DEMO-PRI-%')->orderBy('id')->limit(3)->get();
    $alumnos[0]->update([
        'nombre' => 'Nombre corregido', 'apellido' => 'Apellido corregido',
        'tipo_documento_id' => null, 'numero_documento' => null,
        'domicilio' => 'Domicilio corregido 321', 'ciudad_id' => null, 'cpa' => null,
        'sexo_registral' => null, 'genero' => null,
    ]);
    $alumnos[1]->update(['numero_documento' => '61999123', 'nombre' => 'Otra corrección']);
    $alumnos[2]->update(['tipo_documento_id' => TipoDocumento::query()->where('nombre', 'Pasaporte')->sole()->id, 'numero_documento' => null]);
    $documentoManual = $alumnos[1]->fresh()->attributesToArray();
    $tipoManual = $alumnos[2]->fresh()->attributesToArray();

    $this->seed(AlumnosGruposSeeder::class);

    $corregido = $alumnos[0]->fresh();
    expect($corregido->nombre)->toBe('Nombre corregido')
        ->and($corregido->apellido)->toBe('Apellido corregido')
        ->and($corregido->numero_documento)->toMatch('/^\d{8}$/')
        ->and($corregido->domicilio)->toBe('Domicilio corregido 321')
        ->and($corregido->ciudad_id)->toBeNull()
        ->and($corregido->cpa)->toBeNull()
        ->and($corregido->sexo_registral)->toBeNull()
        ->and($corregido->genero)->toBeNull()
        ->and($alumnos[1]->fresh()->attributesToArray())->toBe($documentoManual)
        ->and($alumnos[2]->fresh()->attributesToArray())->toBe($tipoManual);
});
