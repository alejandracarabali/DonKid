<?php

use App\Models\Child;
use App\Models\User;
use App\Models\Classroom;

// 'classroom_id' => null evita crear grupos extra: la factory de grupos
// usa nombres únicos y se agotaría tras varios tests.

it('permite a una familia ver a su hijo', function () {
    $madre = User::factory()->guardian()->create();
    $hijo = Child::factory()->create(['classroom_id' => null]);
    $hijo->guardians()->attach($madre->id, ['relationship' => 'Mamá']);

    $this->actingAs($madre)
        ->get(route('guardian.children.show', $hijo))
        ->assertOk();
});

it('impide que una familia vea a un niño que no es su hijo', function () {
    $madre = User::factory()->guardian()->create();
    $ajeno = Child::factory()->create(['classroom_id' => null]);

    $this->actingAs($madre)
        ->get(route('guardian.children.show', $ajeno))
        ->assertForbidden();
});

it('permite a una educadora ver a un niño de su grupo', function () {
    $laura = User::factory()->educator()->create();
    $grupo = Classroom::factory()->create();
    $laura->classrooms()->attach($grupo);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id]);

    expect($laura->can('view', $nino))->toBeTrue();
});

it('impide que una educadora vea a un niño de otro grupo', function () {
    $laura = User::factory()->educator()->create();
    $suyo = Classroom::factory()->create();
    $otro = Classroom::factory()->create();
    $laura->classrooms()->attach($suyo);
    $ajeno = Child::factory()->create(['classroom_id' => $otro->id]);

    expect($laura->can('view', $ajeno))->toBeFalse();
});

it('impide que una educadora vea a un niño sin grupo', function () {
    $laura = User::factory()->educator()->create();
    $sinGrupo = Child::factory()->create(['classroom_id' => null]);

    expect($laura->can('view', $sinGrupo))->toBeFalse();
});