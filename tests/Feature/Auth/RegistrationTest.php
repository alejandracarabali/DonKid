<?php

use App\Models\User;

// El registro público está cerrado: solo la coordinación crea cuentas.
it('no permite el registro público', function () {
    $this->get('/register')->assertNotFound();

    $this->post('/register', [
        'name' => 'Intruso',
        'email' => 'intruso@correo.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'admin', // intento de escalar privilegios
    ])->assertNotFound();

    // La prueba que importa: no se creó ninguna cuenta.
    expect(User::where('email', 'intruso@correo.com')->exists())->toBeFalse();
});