<?php

use App\Enums\GradeLevel;
use App\Enums\SchoolType;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use App\Support\Navigation;

// RF-54: la bitácora diaria solo existe en preescolar.

function schoolOfType(SchoolType $type): School
{
    return School::factory()->type($type)->create();
}

function educatorIn(Classroom $classroom): User
{
    $educator = User::factory()->educator()->create(['school_id' => $classroom->school_id]);
    $educator->classrooms()->attach($classroom);

    return $educator;
}

it('en un jardín todos los grupos llevan bitácora', function () {
    $school = schoolOfType(SchoolType::Kindergarten);
    $grupo = Classroom::factory()->level(GradeLevel::Parvulos)->create(['school_id' => $school->id]);

    expect($grupo->hasDailyLog())->toBeTrue();
});

it('en un colegio de primaria ningún grupo lleva bitácora', function () {
    $school = schoolOfType(SchoolType::Primary);
    $grupo = Classroom::factory()->level(GradeLevel::Primero)->create(['school_id' => $school->id]);
    $educator = educatorIn($grupo);

    expect($grupo->hasDailyLog())->toBeFalse()
        ->and(collect(Navigation::for($educator))->pluck('label'))->not->toContain('Bitácora');

    $this->actingAs($educator)->get(route('educator.reports.index'))->assertNotFound();
});

it('en jardín y primaria solo los grupos de preescolar llevan bitácora', function () {
    $school = schoolOfType(SchoolType::Both);

    foreach (GradeLevel::cases() as $level) {
        $grupo = Classroom::factory()->level($level)->create(['school_id' => $school->id, 'name' => 'Grupo '.$level->value]);
        expect($grupo->hasDailyLog())->toBe($level->isPreschool());
    }

    $sinNivel = Classroom::factory()->create(['school_id' => $school->id, 'name' => 'Sin nivel', 'level' => null]);
    expect($sinNivel->hasDailyLog())->toBeFalse();
});

it('la docente no ve ni registra bitácora de un niño de primaria', function () {
    $school = schoolOfType(SchoolType::Both);
    $transicion = Classroom::factory()->level(GradeLevel::Transicion)->create(['school_id' => $school->id]);
    $primero = Classroom::factory()->level(GradeLevel::Primero)->create(['school_id' => $school->id]);
    $educator = educatorIn($transicion);
    $educator->classrooms()->attach($primero);
    Child::factory()->create(['classroom_id' => $transicion->id, 'first_name' => 'Preescolar']);
    $deprimaria = Child::factory()->create(['classroom_id' => $primero->id, 'first_name' => 'Primaria']);

    $this->actingAs($educator)->get(route('educator.reports.index'))
        ->assertOk()->assertSee('Preescolar')->assertDontSee('Primaria');

    $this->actingAs($educator)->get(route('educator.reports.create', $deprimaria))->assertForbidden();
    $this->actingAs($educator)->post(route('educator.reports.store', $deprimaria), ['mood' => 'happy'])->assertForbidden();
});

it('la familia de un niño de primaria no tiene bitácora', function () {
    $school = schoolOfType(SchoolType::Both);
    $primero = Classroom::factory()->level(GradeLevel::Primero)->create(['school_id' => $school->id]);
    $nino = Child::factory()->create(['classroom_id' => $primero->id]);
    $madre = User::factory()->guardian()->create(['school_id' => $school->id]);
    $madre->children()->attach($nino);

    $this->actingAs($madre)->get(route('guardian.dashboard'))
        ->assertOk()->assertDontSee(route('guardian.reports.index', $nino));

    $this->actingAs($madre)->get(route('guardian.reports.index', $nino))->assertNotFound();
});

it('coordinación solo puede crear grupos con niveles de su tipo de institución', function () {
    $school = schoolOfType(SchoolType::Primary);
    $admin = User::factory()->admin()->create(['school_id' => $school->id]);

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Pequeños', 'level' => 'jardin'])
        ->assertSessionHasErrors('level');

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Primero A', 'level' => 'primero'])
        ->assertSessionHasNoErrors();

    expect(Classroom::withoutGlobalScopes()->where('name', 'Primero A')->value('level'))->toBe(GradeLevel::Primero);
});

it('exige el nivel al crear un grupo', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.classrooms.store'), ['name' => 'Sin nivel'])
        ->assertSessionHasErrors('level');
});
