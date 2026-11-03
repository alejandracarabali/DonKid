<?php

use App\Models\Child;
use App\Models\User;

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