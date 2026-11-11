<?php

use App\Enums\Role;
use App\Models\Classroom;
use App\Models\User;

it('crea una educadora con rol educator y la asigna a un grupo', function () {
    $admin = User::factory()->admin()->create();
    $grupo = Classroom::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.educators.store'), [
            'name' => 'Laura Mora',
            'email' => 'lmora@correo.com',
            'classrooms' => [$grupo->id],
            'role' => 'admin', // intento de escalar privilegios: debe ignorarse
        ])
        ->assertRedirect(route('admin.educators.index'))
        ->assertSessionHas('temporal_password');

    $laura = User::where('email', 'lmora@correo.com')->first();

    expect($laura->role)->toBe(Role::Educator)
        ->and($laura->must_change_password)->toBeTrue()
        ->and($laura->classrooms->pluck('id')->all())->toBe([$grupo->id]);
});

it('sincroniza los grupos al editar', function () {
    $admin = User::factory()->admin()->create();
    $educadora = User::factory()->educator()->create();
    [$a, $b] = Classroom::factory()->count(2)->create();
    $educadora->classrooms()->attach($a->id);

    $this->actingAs($admin)
        ->patch(route('admin.educators.update', $educadora), [
            'name' => $educadora->name,
            'email' => $educadora->email,
            'classrooms' => [$b->id],
        ]);

    expect($educadora->fresh()->classrooms->pluck('id')->all())->toBe([$b->id]);
});

it('rechaza un correo repetido', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->educator()->create(['email' => 'repetido@correo.com']);

    $this->actingAs($admin)
        ->post(route('admin.educators.store'), ['name' => 'Otra', 'email' => 'repetido@correo.com'])
        ->assertSessionHasErrors('email');
});

it('no permite editar ni quitar cuentas que no son de educadora', function () {
    $admin = User::factory()->admin()->create();
    $familia = User::factory()->guardian()->create();

    $this->actingAs($admin)->get(route('admin.educators.edit', $familia))->assertNotFound();
    $this->actingAs($admin)->delete(route('admin.educators.destroy', $familia))->assertNotFound();

    expect(User::find($familia->id))->not->toBeNull();
});

it('impide que una educadora o una familia gestionen educadoras', function () {
    $educadora = User::factory()->educator()->create();
    $madre = User::factory()->guardian()->create();

    $this->actingAs($educadora)->get(route('admin.educators.index'))->assertForbidden();
    $this->actingAs($madre)->get(route('admin.educators.index'))->assertForbidden();
});