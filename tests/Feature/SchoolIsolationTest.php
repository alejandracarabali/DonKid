<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;

// Crea un niño en OTRO colegio (distinto al Colegio Demo).
function childFromAnotherSchool(): Child
{
    $otro = School::factory()->create();
    $grupo = Classroom::factory()->create(['school_id' => $otro->id, 'name' => 'Grupo Ajeno']);

    return Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ajeno']);
}

test('coordinación solo ve en el listado los niños de su colegio', function () {
    $admin = User::factory()->admin()->create();
    Child::factory()->create(['first_name' => 'Propio']);
    childFromAnotherSchool();

    $this->actingAs($admin)
        ->get(route('admin.children.index'))
        ->assertOk()
        ->assertSee('Propio')
        ->assertDontSee('Ajeno');
});

test('coordinación recibe 404 al abrir un niño de otro colegio', function () {
    $admin = User::factory()->admin()->create();
    $ajeno = childFromAnotherSchool();

    $this->actingAs($admin)
        ->get(route('admin.children.edit', $ajeno))
        ->assertNotFound();
});

test('coordinación no ve educadoras de otro colegio', function () {
    $admin = User::factory()->admin()->create();
    User::factory()->educator()->create(['name' => 'Docente Propia']);
    User::factory()->educator()->create([
        'name' => 'Docente Ajena',
        'school_id' => School::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.educators.index'))
        ->assertOk()
        ->assertSee('Docente Propia')
        ->assertDontSee('Docente Ajena');
});

test('un grupo nuevo queda en el colegio de quien lo crea', function () {
    $otro = School::factory()->create();
    $admin = User::factory()->admin()->create(['school_id' => $otro->id]);

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Girasoles'])
        ->assertRedirect(route('admin.classrooms.index'));

    $grupo = Classroom::withoutGlobalScopes()->where('name', 'Girasoles')->first();

    expect($grupo->school_id)->toBe($otro->id);
});

test('sin usuario conectado no se filtra (consola, colas y seeders)', function () {
    Child::factory()->create();
    childFromAnotherSchool();

    expect(Child::count())->toBe(2);
});