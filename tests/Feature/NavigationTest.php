<?php

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

test('la docente ve la barra inferior con sus secciones', function () {
    $this->actingAs(User::factory()->educator()->create())
        ->get(route('educator.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Navegación principal"', false)
        ->assertSee('Bitácora')
        ->assertSee('Actividades')
        ->assertDontSee('Menú de coordinación');
});

test('la coordinación ve el menú lateral y no la barra inferior', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Menú de coordinación"', false)
        ->assertSee('Docentes')
        ->assertSee('Estudiantes')
        ->assertDontSee('Navegación principal');
});

test('el menú marca la sección actual', function () {
    $html = $this->actingAs(User::factory()->educator()->create())
        ->get(route('educator.reports.index'))
        ->getContent();

    expect($html)->toMatch('/aria-current="page"\s*>Bitácora</');
});

test('la familia ve cuántos avisos tiene sin leer', function () {
    $familia = User::factory()->guardian()->create();
    DatabaseNotification::create([
        'id' => Str::uuid()->toString(),
        'type' => 'App\Notifications\NewAnnouncement',
        'notifiable_type' => User::class,
        'notifiable_id' => $familia->id,
        'data' => ['title' => 'Paseo'],
    ]);

    $this->actingAs($familia)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('1 sin leer');
});