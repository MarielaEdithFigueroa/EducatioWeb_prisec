<?php

use App\Models\User;
use Illuminate\Support\Facades\Validator;

it('muestra los errores del formulario integrante en castellano con la configuración argentina', function () {
    app()->setLocale('es_AR');
    app('translator')->setFallback('es_AR');

    $this->actingAs(User::factory()->create())
        ->post(route('academico.integrantes.store'), [])
        ->assertSessionHasErrors([
            'legajo' => 'El campo legajo es obligatorio.',
            'apellido' => 'El campo apellido es obligatorio.',
            'nombre' => 'El campo nombre es obligatorio.',
        ]);
});

it('traduce reglas comunes y sus parámetros sin depender del idioma de respaldo', function () {
    app()->setLocale('es_AR');
    app('translator')->setFallback('es_AR');

    $validator = Validator::make([
        'email' => 'incorrecto', 'nombre' => 'Largo', 'estado' => 'inválido',
    ], [
        'email' => ['email'], 'nombre' => ['string', 'max:3'], 'estado' => ['in:activo,inactivo'],
    ]);

    expect($validator->errors()->first('email'))->toBe('El campo correo electrónico debe ser una dirección de correo electrónico válida.')
        ->and($validator->errors()->first('nombre'))->toBe('El campo nombre no debe ser mayor que 3 caracteres.')
        ->and($validator->errors()->first('estado'))->toBe('El estado seleccionado no es válido.');
});

it('resuelve los mensajes generales en castellano argentino', function () {
    expect(__('auth.failed', [], 'es_AR'))->toBe('Las credenciales ingresadas no son correctas.')
        ->and(__('passwords.sent', [], 'es_AR'))->toBe('Te enviamos el enlace de restablecimiento de contraseña por correo electrónico.');
});
