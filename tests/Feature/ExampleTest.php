<?php

use App\Models\User;

test('la raíz lleva al login a quien no ha iniciado sesión', function () {
    $this->get('/')->assertRedirect('/dashboard');
    $this->get('/dashboard')->assertRedirect(route('login'));
});

test('la raíz lleva a cada rol a su zona', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/')
        ->assertRedirect('/dashboard');

    $this->get('/dashboard')->assertRedirect(route('admin.dashboard'));
});