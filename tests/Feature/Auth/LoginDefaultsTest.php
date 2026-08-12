<?php

use Inertia\Testing\AssertableInertia as Assert;

it('preloads the local development credentials', function () {
    $originalEnvironment = app()->environment();
    app()->detectEnvironment(fn () => 'local');

    try {
        $this->get('/login')->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('defaultLogin', 'admin')
            ->where('defaultPassword', 'q'));
    } finally {
        app()->detectEnvironment(fn () => $originalEnvironment);
    }
});

it('does not expose development credentials outside local', function () {
    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->component('auth/login')
        ->where('defaultLogin', '')
        ->where('defaultPassword', ''));
});
