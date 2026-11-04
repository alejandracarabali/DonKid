<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// 1. Camino feliz: coordinación crea un niño con grupo.
it('permite a coordinación crear un niño', function () {
    $admin = User::factory()->admin()->create();
    $grupo = Classroom::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.children.store'), [
            'first_name' => 'Sofía',
            'last_name' => 'Gómez',
            'birth_date' => '2023-05-10',
            'classroom_id' => $grupo->id,
        ])
        ->assertRedirect(route('admin.children.index'));

    expect(Child::where('first_name', 'Sofía')->exists())->toBeTrue();
});

// 2. Validación: campos obligatorios.
it('exige nombre, apellido y fecha de nacimiento', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.children.store'), [])
        ->assertSessionHasErrors(['first_name', 'last_name', 'birth_date']);
});

// 3. Nadie nace en el futuro.
it('rechaza una fecha de nacimiento futura', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.children.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
            'birth_date' => now()->addDay()->toDateString(),
        ])
        ->assertSessionHasErrors('birth_date');
});

// 4. Un grupo inexistente enviado a mano es rechazado en el servidor.
it('rechaza un grupo que no existe', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.children.store'), [
            'first_name' => 'Ana',
            'last_name' => 'Ruiz',
            'birth_date' => '2023-01-01',
            'classroom_id' => 9999,
        ])
        ->assertSessionHasErrors('classroom_id');
});

// 5. Al quitar un niño se borran también sus vínculos con acudientes.
it('borra los vínculos con acudientes al quitar un niño', function () {
    $admin = User::factory()->admin()->create();
    $madre = User::factory()->guardian()->create();
    $nino = Child::factory()->create(['classroom_id' => null]);
    $nino->guardians()->attach($madre->id, ['relationship' => 'Mamá']);

    $this->actingAs($admin)->delete(route('admin.children.destroy', $nino));

    expect(Child::find($nino->id))->toBeNull()
        ->and(User::find($madre->id))->not->toBeNull() // la madre sigue existiendo
        ->and(DB::table('child_guardian')->count())->toBe(0);
});

// 6. Privacidad: una familia no gestiona niños.
it('impide que una familia gestione niños', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)
        ->get(route('admin.children.index'))
        ->assertForbidden();

    $this->actingAs($madre)
        ->post(route('admin.children.store'), [
            'first_name' => 'Intruso',
            'last_name' => 'X',
            'birth_date' => '2023-01-01',
        ])
        ->assertForbidden();

    expect(Child::where('first_name', 'Intruso')->exists())->toBeFalse();
});

// 7. Una educadora tampoco gestiona niños (solo coordinación).
it('impide que una educadora gestione niños', function () {
    $educadora = User::factory()->educator()->create();

    $this->actingAs($educadora)
        ->get(route('admin.children.index'))
        ->assertForbidden();
});