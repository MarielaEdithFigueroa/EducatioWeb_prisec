<?php

use App\Models\CondicionEspecial;
use Database\Seeders\CondicionesEspecialesSeeder;

it('siembra las condiciones especiales confirmadas de forma idempotente', function () {
    $this->seed(CondicionesEspecialesSeeder::class);

    $ids = CondicionEspecial::query()->orderBy('id')->pluck('id')->all();

    $this->seed(CondicionesEspecialesSeeder::class);

    expect(CondicionEspecial::query()->count())->toBe(16)
        ->and(CondicionEspecial::query()->orderBy('id')->pluck('id')->all())->toBe($ids)
        ->and(CondicionEspecial::query()->whereKey(14)->value('descripcion'))
        ->toBe('Condición emocional o de salud mental que pueda requerir acompañamiento particular')
        ->and(CondicionEspecial::query()->whereKey(15)->value('descripcion'))
        ->toBe('Enfermedad crónica que pueda afectar su asistencia o proceso de aprendizaje')
        ->and(CondicionEspecial::query()->whereKey(16)->value('descripcion'))
        ->toBe('Alergias u otras condiciones de salud relevantes para su permanencia en la institución')
        ->and(CondicionEspecial::query()->whereIn('id', [14, 15, 16])->get()->every(
            fn (CondicionEspecial $condicionEspecial) => $condicionEspecial->activo,
        ))->toBeTrue();
});
