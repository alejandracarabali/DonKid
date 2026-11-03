<?php

use App\Models\User;

// 1. Cada rol entra a SU zona.
// "with" ejecuta el mismo test una vez por cada fila de datos.
it('permite a cada rol entrar a su zona', function (string $estado, string $ruta) {
    $usuario = User::factory()->{$estado}()->create();

    $this->actingAs($usuario)
        ->get(route($ruta))
        ->assertOk();
})->with([
    'coordinación' => ['admin', 'admin.dashboard'],
    'educadora'    => ['educator', 'educator.dashboard'],
    'familia'      => ['guardian', 'guardian.dashboard'],
]);

// 2. Lo más importante: nadie entra donde no debe.
it('impide que una familia entre al panel de coordinación', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)
        ->get(route('admin.dashboard'))
        ->assertForbidden(); // 403
});

it('impide que una familia entre a la zona de educadoras', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)
        ->get(route('educator.dashboard'))
        ->assertForbidden();
});

it('impide que una educadora entre al panel de coordinación', function () {
    $educadora = User::factory()->educator()->create();

    $this->actingAs($educadora)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

// 3. Un visitante sin sesión va al login.
it('manda a un visitante sin sesión al login', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect('/login');
});

// 4. /dashboard reparte a cada rol a su zona.
it('redirige cada rol a su zona desde /dashboard', function () {
    $educadora = User::factory()->educator()->create();

    $this->actingAs($educadora)
        ->get('/dashboard')
        ->assertRedirect(route('educator.dashboard'));
});

// 5. Seguridad: el registro NO puede crear administradores.
it('ignora el campo role en el registro', function () {
    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@correo.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin', // intento de escalar privilegios
    ]);

    expect(User::where('email', 'intruso@correo.com')->first()->role->value)
        ->toBe('guardian');
});