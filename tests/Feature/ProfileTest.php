<?php

use App\Models\User;

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test User', $user->name);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', [
            'name' => 'Test User',
            'email' => $user->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

// Nadie borra su propia cuenta: las cuentas las administra coordinación.
test('nadie puede borrar su propia cuenta', function (string $role) {
    $user = User::factory()->{$role}()->create();

    $this->actingAs($user)
        ->delete('/profile', ['password' => 'password'])
        ->assertStatus(405); // 405 = la ruta /profile existe, pero no acepta DELETE

    expect($user->fresh())->not->toBeNull();
})->with(['admin', 'educator', 'guardian']);

test('el perfil no muestra la opción de borrar la cuenta', function () {
    $user = User::factory()->guardian()->create();

    $this->actingAs($user)
        ->get('/profile')
        ->assertOk()
        ->assertDontSee('Borrar cuenta')
        ->assertSee('escríbele a coordinación');
});