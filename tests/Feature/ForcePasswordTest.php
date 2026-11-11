<?php

use App\Models\User;

it('obliga a cambiar la clave temporal antes de usar la app', function () {
    $familia = User::factory()->guardian()->create(['must_change_password' => true]);

    $this->actingAs($familia)
        ->get(route('guardian.dashboard'))
        ->assertRedirect(route('password.force'));
});

it('libera el acceso después de cambiar la clave', function () {
    $familia = User::factory()->guardian()->create([
        'must_change_password' => true,
        'password' => 'Temporal123',
    ]);

    $this->actingAs($familia)
        ->put(route('password.force.update'), [
            'current_password' => 'Temporal123',
            'password' => 'ClaveNueva456',
            'password_confirmation' => 'ClaveNueva456',
        ])
        ->assertRedirect(route('dashboard'));

    expect($familia->fresh()->must_change_password)->toBeFalse();

    $this->actingAs($familia->fresh())
        ->get(route('guardian.dashboard'))
        ->assertOk();
});

it('rechaza si la clave actual es incorrecta', function () {
    $familia = User::factory()->guardian()->create(['must_change_password' => true]);

    $this->actingAs($familia)
        ->put(route('password.force.update'), [
            'current_password' => 'incorrecta',
            'password' => 'ClaveNueva456',
            'password_confirmation' => 'ClaveNueva456',
        ])
        ->assertSessionHasErrors('current_password');

    expect($familia->fresh()->must_change_password)->toBeTrue();
});

it('rechaza reutilizar la clave temporal', function () {
    $familia = User::factory()->guardian()->create([
        'must_change_password' => true,
        'password' => 'Temporal123',
    ]);

    $this->actingAs($familia)
        ->put(route('password.force.update'), [
            'current_password' => 'Temporal123',
            'password' => 'Temporal123',
            'password_confirmation' => 'Temporal123',
        ])
        ->assertSessionHasErrors('password');
});

it('marca como pendiente la clave de una familia recién creada', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.guardians.store'), [
        'name' => 'Rosa Díaz',
        'email' => 'rosa@correo.com',
    ]);

    expect(User::where('email', 'rosa@correo.com')->first()->must_change_password)->toBeTrue();
});

it('usa la palabra contraseña en la pantalla de cambio obligatorio', function () {
    $familia = User::factory()->guardian()->create(['must_change_password' => true]);

    $this->actingAs($familia)
        ->get(route('password.force'))
        ->assertOk()
        ->assertSee('Crea tu contraseña nueva')
        ->assertDontSee('Clave que te dieron');
});