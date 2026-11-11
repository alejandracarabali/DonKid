<?php

test('el login muestra la marca DonKid con vocabulario neutro', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Tu colegio, más cerca de casa.')
        ->assertDontSee('jardín, más cerca');
});

test('el login tiene un solo botón para ingresar', function () {
    $html = $this->get('/login')->getContent();

    // Un formulario, un botón de envío: evita restos de la vista original de Breeze.
    expect(substr_count($html, 'type="submit"'))->toBe(1);
});