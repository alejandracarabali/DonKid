<?php

use App\Enums\GradeLevel;
use App\Enums\SchoolType;
use App\Enums\SubjectKind;
use App\Models\Classroom;
use App\Models\PerformanceLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;

// RF-04: materias y dimensiones. RF-05: escala de desempeños.

function adminOf(School $school): User
{
    return User::factory()->admin()->create(['school_id' => $school->id]);
}

it('un colegio nuevo arranca con las áreas obligatorias, las dimensiones y la escala', function () {
    $school = School::factory()->create(['type' => SchoolType::Both]);
    test()->actingAs(adminOf($school));

    expect(Subject::where('kind', SubjectKind::Subject)->count())->toBe(10)
        ->and(Subject::where('kind', SubjectKind::Dimension)->count())->toBe(7)
        ->and(PerformanceLevel::orderBy('position')->pluck('name')->all())->toBe(['Bajo', 'Básico', 'Alto', 'Superior']);
});

it('un jardín solo recibe dimensiones y una primaria solo materias', function () {
    $kinder = School::factory()->type(SchoolType::Kindergarten)->create();
    $primary = School::factory()->type(SchoolType::Primary)->create();

    expect(Subject::withoutGlobalScopes()->where('school_id', $kinder->id)->pluck('kind')->unique()->all())->toBe([SubjectKind::Dimension])
        ->and(Subject::withoutGlobalScopes()->where('school_id', $primary->id)->pluck('kind')->unique()->all())->toBe([SubjectKind::Subject]);
});

it('convierte una nota en su desempeño', function (float $grade, string $level) {
    test()->actingAs(User::factory()->admin()->create());

    expect(PerformanceLevel::for($grade)?->name)->toBe($level);
})->with([[1.0, 'Bajo'], [2.9, 'Bajo'], [3.0, 'Básico'], [3.9, 'Básico'], [4.0, 'Alto'], [4.5, 'Alto'], [4.6, 'Superior'], [5.0, 'Superior']]);

it('coordinación agrega, renombra y desactiva materias', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('admin.subjects.index'))->assertOk()->assertSee('Matemáticas');

    $this->post(route('admin.subjects.store'), ['kind' => 'subject', 'name' => 'Cátedra de la paz'])->assertSessionHasNoErrors();
    $subject = Subject::where('name', 'Cátedra de la paz')->firstOrFail();

    $this->patch(route('admin.subjects.update', $subject), ['name' => 'Cátedra de paz'])->assertSessionHasNoErrors();
    $this->post(route('admin.subjects.toggle', $subject));

    expect($subject->fresh())->name->toBe('Cátedra de paz')->active->toBeFalse();
});

it('no deja repetir el nombre de una materia', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.subjects.store'), ['kind' => 'subject', 'name' => 'Matemáticas'])
        ->assertSessionHasErrors(['name' => 'Ya existe con ese nombre.']);
});

it('un jardín no puede crear materias de primaria', function () {
    $this->actingAs(adminOf(School::factory()->type(SchoolType::Kindergarten)->create()))
        ->post(route('admin.subjects.store'), ['kind' => 'subject', 'name' => 'Álgebra'])
        ->assertSessionHasErrors('kind');
});

it('no se pueden editar materias de otro colegio', function () {
    $other = Subject::withoutGlobalScopes()->where('school_id', School::factory()->create()->id)->first();

    $this->actingAs(User::factory()->admin()->create())
        ->patch(route('admin.subjects.update', $other->id), ['name' => 'Hackeada'])
        ->assertNotFound();
});

it('coordinación cambia la escala de desempeños', function () {
    $this->actingAs(User::factory()->admin()->create());
    $ids = PerformanceLevel::orderBy('position')->pluck('id');
    $ranges = [[1.0, 3.4], [3.5, 3.9], [4.0, 4.5], [4.6, 5.0]];

    $this->put(route('admin.performance.update'), ['levels' => $ids->combine(
        collect($ranges)->map(fn ($r) => ['min' => $r[0], 'max' => $r[1]])
    )->all()])->assertSessionHasNoErrors();

    expect(PerformanceLevel::for(3.2)->name)->toBe('Bajo');
});

it('rechaza una escala con huecos o que no llega a 5.0', function (array $ranges) {
    $this->actingAs(User::factory()->admin()->create());
    $ids = PerformanceLevel::orderBy('position')->pluck('id');

    $this->put(route('admin.performance.update'), ['levels' => $ids->combine(
        collect($ranges)->map(fn ($r) => ['min' => $r[0], 'max' => $r[1]])
    )->all()])->assertSessionHasErrors();

    expect(PerformanceLevel::for(2.9)->name)->toBe('Bajo'); // no cambió nada
})->with([
    'hueco' => [[[1.0, 2.9], [3.1, 3.9], [4.0, 4.5], [4.6, 5.0]]],
    'cruce' => [[[1.0, 3.2], [3.0, 3.9], [4.0, 4.5], [4.6, 5.0]]],
    'no llega a 5' => [[[1.0, 2.9], [3.0, 3.9], [4.0, 4.5], [4.6, 4.9]]],
    'dos decimales' => [[[1.0, 2.95], [3.0, 3.9], [4.0, 4.5], [4.6, 5.0]]],
]);

it('asigna materias y docente por materia a un grupo de primaria', function () {
    $admin = User::factory()->admin()->create();
    $teacher = User::factory()->educator()->create();
    $group = Classroom::factory()->create(['level' => GradeLevel::Tercero]);
    $math = Subject::where('name', 'Matemáticas')->first();
    $english = Subject::where('name', 'Inglés')->first();

    $this->actingAs($admin)->get(route('admin.classrooms.subjects', $group))->assertOk()->assertSee('Matemáticas')->assertDontSee('Cognitiva');

    $this->put(route('admin.classrooms.subjects.update', $group), ['subjects' => [
        $math->id => ['on' => 1, 'educator_id' => $teacher->id],
        $english->id => ['on' => 1, 'educator_id' => ''],
    ]])->assertRedirect(route('admin.classrooms.index'));

    expect($group->subjects()->pluck('educator_id', 'subjects.id')->all())->toBe([$math->id => $teacher->id, $english->id => null])
        ->and($group->educators()->whereKey($teacher->id)->exists())->toBeTrue(); // el docente ya ve el grupo
});

it('un grupo de preescolar trabaja con dimensiones, no con materias', function () {
    $this->actingAs(User::factory()->admin()->create());
    $group = Classroom::factory()->create(['level' => GradeLevel::Jardin]);
    $math = Subject::where('name', 'Matemáticas')->first();

    $this->get(route('admin.classrooms.subjects', $group))->assertSee('Cognitiva')->assertDontSee('Matemáticas');
    $this->put(route('admin.classrooms.subjects.update', $group), ['subjects' => [$math->id => ['on' => 1]]]);

    expect($group->subjects()->count())->toBe(0);
});

it('no se asigna como docente a alguien de otro colegio', function () {
    $this->actingAs(User::factory()->admin()->create());
    $outsider = User::factory()->educator()->create(['school_id' => School::factory()->create()->id]);
    $group = Classroom::factory()->create(['level' => GradeLevel::Primero]);
    $math = Subject::where('name', 'Matemáticas')->first();

    $this->put(route('admin.classrooms.subjects.update', $group), ['subjects' => [$math->id => ['on' => 1, 'educator_id' => $outsider->id]]])
        ->assertSessionHasErrors();
});

it('docentes y familias no entran a materias ni escala', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create());

    $this->get(route('admin.subjects.index'))->assertForbidden();
    $this->get(route('admin.performance.edit'))->assertForbidden();
})->with(['educator', 'guardian']);
