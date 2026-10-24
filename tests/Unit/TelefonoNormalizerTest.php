<?php

use App\Services\TelefonoNormalizer;

it('normaliza teléfonos a E.164 según el tipo elegido', function (?string $entrada, bool $esLineaFija, ?string $esperado) {
    expect(TelefonoNormalizer::normalizarArgentina($entrada, $esLineaFija))->toBe($esperado);
})->with([
    'vacío' => [null, false, null],
    'nacional como móvil por defecto' => ['299 5123456', false, '+5492995123456'],
    'nacional con cero y quince como móvil' => ['0299 15 5123456', false, '+5492995123456'],
    'nacional marcado como fijo' => ['299 5123456', true, '+542995123456'],
    'móvil internacional incompatible con fijo' => ['+54 9 299 5123456', true, null],
    'móvil con quince incompatible con fijo' => ['0299 15 5123456', true, null],
    'fijo que contiene quince conserva su tipo' => ['0299 4515123', true, '+542994515123'],
    'fijo internacional convertido a móvil por defecto' => ['+54 299 5123456', false, '+5492995123456'],
    'fijo con prefijo 00 convertido a móvil por defecto' => ['0054 299 5123456', false, '+5492995123456'],
    'móvil posible aunque el rango no esté asignado' => ['+54 9 299 1234567', false, '+5492991234567'],
    'internacional extranjero no se modifica' => ['+598 99 123456', false, '+59899123456'],
    'línea gratuita no se fuerza como móvil' => ['0800-333-1234', false, '+548003331234'],
    'demasiado corto' => ['911', false, null],
    'extensión sin campo separado' => ['299 5123456 interno 12', false, null],
]);

it('reconoce móviles argentinos sin confundir un quince dentro de un fijo', function (string $telefono, bool $esperado) {
    expect(TelefonoNormalizer::esMovilArgentina($telefono))->toBe($esperado);
})->with([
    'móvil con prefijo quince' => ['0299 15 5123456', true],
    'móvil internacional' => ['+54 9 299 5123456', true],
    'fijo con quince en el abonado' => ['0299 4515123', false],
]);

it('determina si un teléfono tiene una estructura posible', function (string $telefono, bool $esperado) {
    expect(TelefonoNormalizer::esPosible($telefono))->toBe($esperado);
})->with([
    'posible' => ['+5492995123456', true],
    'imposible' => ['123', false],
]);

it('reconstruye el indicador de línea fija desde el E.164 guardado', function (string $telefono, bool $esperado) {
    expect(TelefonoNormalizer::esLineaFija($telefono))->toBe($esperado);
})->with([
    'fijo argentino' => ['+542995123456', true],
    'móvil argentino' => ['+5492995123456', false],
    'fijo extranjero' => ['+59829012345', true],
    'móvil extranjero' => ['+59899123456', false],
]);

it('extrae solamente los dígitos para búsquedas parciales', function () {
    expect(TelefonoNormalizer::soloDigitos('+54 9 (299) 512-3456'))->toBe('5492995123456');
});
