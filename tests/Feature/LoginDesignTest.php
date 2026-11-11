<?php

test('el login muestra la marca DonKid con vocabulario neutro', function () {
    $this->get('/login')
        ->assertOk()
        ->assertSee('Tu colegio, más cerca de casa.')
        ->assertDontSee('jardín, más cerca');
});