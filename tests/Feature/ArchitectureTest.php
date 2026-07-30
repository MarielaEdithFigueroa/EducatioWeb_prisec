<?php

use Illuminate\Support\Facades\Schema;

it('keeps activo immediately after id', function () {
    $tables = collect(Schema::getTables())
        ->pluck('name')
        ->filter(fn (string $table) => in_array('activo', Schema::getColumnListing($table)));

    foreach ($tables as $table) {
        expect(array_search('activo', Schema::getColumnListing($table)))
            ->toBe(1, "En '$table', activo debe ser la segunda columna.");
    }
});
