<?php

use App\Models\User;

test('la guía de estilos muestra los componentes base', function () {
    $user = User::factory()->educator()->create();

    $this->actingAs($user)
        ->get('/estilos')
        ->assertOk()
        ->assertSee('Botón principal: Enviar a la familia')
        ->assertSee('Enviado');
});

test('un visitante sin sesión no puede ver la guía de estilos', function () {
    $this->get('/estilos')->assertRedirect('/login');
});