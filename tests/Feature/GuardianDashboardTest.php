<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

it('muestra a la familia solo sus hijos con enlace a la bitácora', function () {
    $guardian = User::factory()->guardian()->create();
    $mine = Child::factory()->create(['first_name' => 'Sofía', 'classroom_id' => Classroom::factory()->create()->id]);
    $other = Child::factory()->create(['first_name' => 'Ajenito']);
    $guardian->children()->attach($mine);

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('Sofía')
        ->assertSee(route('guardian.reports.index', $mine))
        ->assertDontSee('Ajenito');
});

it('avisa a una familia sin hijos vinculados', function () {
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)
        ->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('Aún no tienes hijos vinculados');
});