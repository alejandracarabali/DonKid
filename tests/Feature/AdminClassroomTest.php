<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

it('permite a coordinación crear un grupo', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Mariposas'])
        ->assertRedirect(route('admin.classrooms.index'));

    expect(Classroom::where('name', 'Mariposas')->exists())->toBeTrue();
});

it('rechaza un nombre de grupo repetido', function () {
    $admin = User::factory()->admin()->create();
    Classroom::factory()->create(['name' => 'Mariposas']);

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Mariposas'])
        ->assertSessionHasErrors('name');
});

it('deja a los niños sin grupo cuando se quita su grupo', function () {
    $admin = User::factory()->admin()->create();
    $grupo = Classroom::factory()->create();
    $nino = Child::factory()->create(['classroom_id' => $grupo->id]);

    $this->actingAs($admin)
        ->delete(route('admin.classrooms.destroy', $grupo))
        ->assertRedirect();

    // El niño sigue existiendo, solo sin grupo.
    expect($nino->fresh())->not->toBeNull()
        ->and($nino->fresh()->classroom_id)->toBeNull();
});

it('impide que una familia gestione grupos', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)
        ->get(route('admin.classrooms.index'))
        ->assertForbidden();

    $this->actingAs($madre)
        ->post(route('admin.classrooms.store'), ['name' => 'Intruso'])
        ->assertForbidden();
});