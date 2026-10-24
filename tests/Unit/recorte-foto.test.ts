import assert from 'node:assert/strict';
import { test } from 'node:test';
import { recorteCuadrado } from '../../resources/js/lib/recorte-foto.ts';

test('centra el recorte en fotos horizontales y verticales sin deformarlas', () => {
    assert.deepEqual(recorteCuadrado(1200, 800, 1, 0.5, 0.5), {
        x: 200,
        y: 0,
        lado: 800,
    });
    assert.deepEqual(recorteCuadrado(800, 1200, 1, 0.5, 0.5), {
        x: 0,
        y: 200,
        lado: 800,
    });
});

test('el zoom reduce el área original y permite llegar a las esquinas', () => {
    assert.deepEqual(recorteCuadrado(1200, 800, 2, 1, 1), {
        x: 800,
        y: 400,
        lado: 400,
    });
    assert.deepEqual(recorteCuadrado(1200, 800, 2, 0, 0), {
        x: 0,
        y: 0,
        lado: 400,
    });
});

test('ningún encuadre sale de los límites de la imagen', () => {
    for (const [ancho, alto] of [
        [512, 512],
        [6000, 100],
        [100, 6000],
    ]) {
        for (const zoom of [0.5, 1, 1.5, 4]) {
            for (const posicion of [-1, 0, 0.5, 1, 2]) {
                const area = recorteCuadrado(
                    ancho,
                    alto,
                    zoom,
                    posicion,
                    posicion,
                );
                assert.ok(area.x >= 0 && area.y >= 0);
                assert.ok(area.x + area.lado <= ancho);
                assert.ok(area.y + area.lado <= alto);
            }
        }
    }
});

test('rechaza dimensiones inexistentes o no finitas', () => {
    assert.throws(() => recorteCuadrado(0, 100, 1, 0.5, 0.5));
    assert.throws(() => recorteCuadrado(100, NaN, 1, 0.5, 0.5));
});
