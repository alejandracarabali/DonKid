<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

// Docente con un grupo asignado y sus estudiantes.
function educatorWithGroup(int $students = 2): array
{
    $docente = User::factory()->educator()->create();
    $grupo = Classroom::factory()->create(['name' => 'Caminadores']);
    $docente->classrooms()->attach($grupo);
    $children = Child::factory()->count($students)->create(['classroom_id' => $grupo->id]);

    return [$docente, $grupo, $children];
}

test('la docente ve la barra con las 5 pestañas del diseño', function () {
    $this->actingAs(User::factory()->educator()->create())
        ->get(route('educator.reports.index'))
        ->assertOk()
        ->assertSeeInOrder(['Bitácora', 'Grupos', 'Actividades', 'Avisos', 'Mensajes']);
});

test('el inicio de la docente lleva a Bitácora', function () {
    $this->actingAs(User::factory()->educator()->create())
        ->get(route('educator.dashboard'))
        ->assertRedirect(route('educator.reports.index'));
});

test('la docente ve solo sus grupos, pendientes al empezar el día', function () {
    [$docente] = educatorWithGroup();
    Classroom::factory()->create(['name' => 'Grupo Ajeno']);

    $this->actingAs($docente)
        ->get(route('educator.groups.index'))
        ->assertOk()
        ->assertSee('Caminadores')
        ->assertSee('Pendiente')
        ->assertDontSee('Grupo Ajeno');
});

test('guarda la asistencia y el grupo queda al día', function () {
    [$docente, $grupo, $children] = educatorWithGroup();

    $this->actingAs($docente)
        ->post(route('educator.groups.attendance.store', $grupo), [
            'status' => [
                $children[0]->id => 'present',
                $children[1]->id => 'absent',
            ],
        ])
        ->assertRedirect(route('educator.groups.attendance', $grupo));

    expect(Attendance::count())->toBe(2)
        ->and(Attendance::first()->recorded_by)->toBe($docente->id);

    $this->get(route('educator.groups.index'))->assertSee('✓ Al día');
});

test('guardar otra vez actualiza en lugar de duplicar', function () {
    [$docente, $grupo, $children] = educatorWithGroup(1);
    $id = $children[0]->id;

    $this->actingAs($docente)->post(route('educator.groups.attendance.store', $grupo), ['status' => [$id => 'present']]);
    $this->actingAs($docente)->post(route('educator.groups.attendance.store', $grupo), ['status' => [$id => 'late']]);

    expect(Attendance::count())->toBe(1)
        ->and(Attendance::first()->status)->toBe(AttendanceStatus::Late);
});

test('ignora estudiantes de otro grupo agregados a mano', function () {
    [$docente, $grupo, $children] = educatorWithGroup(1);
    $ajeno = Child::factory()->create();

    $this->actingAs($docente)->post(route('educator.groups.attendance.store', $grupo), [
        'status' => [$children[0]->id => 'present', $ajeno->id => 'absent'],
    ]);

    expect(Attendance::where('child_id', $ajeno->id)->exists())->toBeFalse();
});

test('rechaza un estado que no existe', function () {
    [$docente, $grupo, $children] = educatorWithGroup(1);
    $id = $children[0]->id;

    $this->actingAs($docente)
        ->post(route('educator.groups.attendance.store', $grupo), ['status' => [$id => 'dormido']])
        ->assertSessionHasErrors("status.$id");
});

test('una docente no abre ni guarda la asistencia de un grupo que no es suyo', function () {
    [$docente] = educatorWithGroup();
    $ajeno = Classroom::factory()->create();

    $this->actingAs($docente)->get(route('educator.groups.attendance', $ajeno))->assertForbidden();
    $this->actingAs($docente)->post(route('educator.groups.attendance.store', $ajeno), ['status' => []])->assertForbidden();
});

test('una familia no entra a Grupos', function () {
    $this->actingAs(User::factory()->guardian()->create())
        ->get(route('educator.groups.index'))
        ->assertForbidden();
});