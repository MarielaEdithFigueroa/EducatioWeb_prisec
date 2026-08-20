<?php

use App\Models\TipoVinculo;
use Database\Seeders\TiposVinculoSeeder;

it('siembra los tipos de vínculo confirmados de forma idempotente', function () {
    $this->seed(TiposVinculoSeeder::class);

    $ids = TipoVinculo::query()->orderBy('id')->pluck('id')->all();

    $this->seed(TiposVinculoSeeder::class);

    expect(TipoVinculo::query()->count())->toBe(14)
        ->and(TipoVinculo::query()->orderBy('id')->pluck('id')->all())->toBe($ids)
        ->and(TipoVinculo::query()->whereKey(4)->value('nombre'))->toBe('Nuevo Papá')
        ->and(TipoVinculo::query()->whereKey(5)->value('nombre'))->toBe('Nueva Mamá')
        ->and(TipoVinculo::query()->whereKey(15)->value('nombre'))->toBe('Otro parentesco');
});
