<?php

use App\Enums\Role;
use App\Models\User;

// El rol se nombra "Docente": sirve igual para hombres y mujeres.
it('nombra el rol como Docente', function () {
    expect(Role::Educator->label())->toBe('Docente');
});

it('muestra el botón Nuevo docente con el estilo de DonKid', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/docentes')
        ->assertOk()
        ->assertSee('Docentes')
        ->assertSee('+ Nuevo docente')
        ->assertSee('class="btn btn-sm"', false)
        ->assertDontSee('educadora');
});

// Los botones de "crear" usan la clase .btn (ámbar del logo), no el gris por defecto.
it('usa el botón de DonKid en las pantallas de crear', function (string $role, string $url) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->get($url)
        ->assertOk()
        ->assertSee('class="btn btn-sm"', false)
        ->assertDontSee('bg-gray-800', false);
})->with([
    ['admin', '/admin/grupos'],
    ['admin', '/admin/ninos'],
    ['admin', '/admin/familias'],
    ['admin', '/admin/docentes'],
    ['educator', '/docente/actividades'],
]);
