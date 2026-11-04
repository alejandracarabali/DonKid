<?php

use App\Enums\Role;
use App\Models\Child;
use App\Models\User;

it('crea una familia con rol guardian y la vincula a un niño', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create(['classroom_id' => null]);

    $this->actingAs($admin)
        ->post(route('admin.guardians.store'), [
            'name' => 'María Pérez',
            'email' => 'maria@correo.com',
            'children' => [$nino->id],
            'relationships' => [$nino->id => 'Mamá'],
            'role' => 'admin', // intento de escalar privilegios: debe ignorarse
        ])
        ->assertRedirect(route('admin.guardians.index'))
        ->assertSessionHas('temporal_password');

    $maria = User::where('email', 'maria@correo.com')->first();

    expect($maria->role)->toBe(Role::Guardian)
        ->and($maria->children->pluck('id')->all())->toBe([$nino->id])
        ->and($maria->children->first()->pivot->relationship)->toBe('Mamá');
});

it('rechaza un correo repetido', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->guardian()->create(['email' => 'repetido@correo.com']);

    $this->actingAs($admin)
        ->post(route('admin.guardians.store'), [
            'name' => 'Otra', 'email' => 'repetido@correo.com',
        ])
        ->assertSessionHasErrors('email');
});

it('sincroniza los niños al editar', function () {
    $admin = User::factory()->admin()->create();
    $familia = User::factory()->guardian()->create();
    [$a, $b] = Child::factory()->count(2)->create(['classroom_id' => null]);
    $familia->children()->attach($a->id);

    $this->actingAs($admin)
        ->patch(route('admin.guardians.update', $familia), [
            'name' => $familia->name,
            'email' => $familia->email,
            'children' => [$b->id],
        ]);

    expect($familia->fresh()->children->pluck('id')->all())->toBe([$b->id]);
});

it('no permite editar ni quitar cuentas que no son de familia', function () {
    $admin = User::factory()->admin()->create();
    $educadora = User::factory()->educator()->create();

    $this->actingAs($admin)
        ->get(route('admin.guardians.edit', $educadora))->assertNotFound();

    $this->actingAs($admin)
        ->delete(route('admin.guardians.destroy', $educadora))->assertNotFound();

    expect(User::find($educadora->id))->not->toBeNull();
});

it('impide que una familia gestione cuentas', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)
        ->get(route('admin.guardians.index'))->assertForbidden();
});