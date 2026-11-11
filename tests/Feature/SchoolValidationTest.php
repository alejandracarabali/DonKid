<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;

// Grupo de OTRO colegio, con un niño adentro.
function otherSchoolClassroom(string $name = 'Transición'): Classroom
{
    $otro = School::factory()->create();
    $grupo = Classroom::factory()->create(['school_id' => $otro->id, 'name' => $name]);
    Child::factory()->create(['classroom_id' => $grupo->id]);

    return $grupo;
}

test('dos colegios pueden tener un grupo con el mismo nombre', function () {
    otherSchoolClassroom('Transición');
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Transición'])
        ->assertSessionHasNoErrors();

    expect(Classroom::withoutGlobalScopes()->where('name', 'Transición')->count())->toBe(2);
});

test('no se puede poner un niño en un grupo de otro colegio', function () {
    $ajeno = otherSchoolClassroom();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.children.store'), [
            'classroom_id' => $ajeno->id,
            'first_name' => 'Intruso',
            'last_name' => 'Prueba',
            'birth_date' => '2022-05-10',
        ])
        ->assertSessionHasErrors('classroom_id');

    expect(Child::withoutGlobalScopes()->where('first_name', 'Intruso')->exists())->toBeFalse();
});

test('no se puede asignar a una educadora un grupo de otro colegio', function () {
    $ajeno = otherSchoolClassroom();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.educators.store'), [
            'name' => 'Docente Nueva',
            'email' => 'docente.nueva@donkid.test',
            'classrooms' => [$ajeno->id],
        ])
        ->assertSessionHasErrors('classrooms.0');
});

test('no se puede vincular una familia con un niño de otro colegio', function () {
    // Aún no hay nadie conectado, así que no hay filtro: se puede leer el niño ajeno.
    $ninoAjeno = otherSchoolClassroom()->children()->first();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.guardians.store'), [
            'name' => 'Familia Nueva',
            'email' => 'familia.nueva@donkid.test',
            'children' => [$ninoAjeno->id],
        ])
        ->assertSessionHasErrors('children.0');
});

test('una docente no puede publicar un aviso en un grupo de otro colegio', function () {
    $ajeno = otherSchoolClassroom();
    $educator = User::factory()->educator()->create();

    $this->actingAs($educator)
        ->post(route('educator.announcements.store'), [
            'classroom_id' => $ajeno->id,
            'title' => 'Intruso',
            'body' => 'No debería poder.',
        ])
        ->assertSessionHasErrors('classroom_id');
});