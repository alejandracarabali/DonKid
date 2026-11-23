<?php

use App\Enums\GradeLevel;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\DimensionReport;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\User;
use App\Support\PeriodGrades;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// RF-07: notas de primaria. RF-08: informes de dimensiones en preescolar.

beforeEach(function () {
    Carbon::setTestNow('2027-03-10');
    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin)->post(route('admin.school-years.store'), [
        'year' => 2027, 'starts_on' => '2027-01-26', 'ends_on' => '2027-11-26',
    ]);
    $this->teacher = User::factory()->educator()->create();
});

afterEach(fn () => Carbon::setTestNow());

// Grupo con una materia asignada al docente del test.
function teachingSetup(GradeLevel $level = GradeLevel::Tercero, string $subject = 'Matemáticas'): array
{
    $group = Classroom::factory()->create(['level' => $level]);
    $subject = Subject::where('name', $subject)->firstOrFail();
    $group->subjects()->attach($subject->id, ['educator_id' => test()->teacher->id]);
    $ana = Child::factory()->create(['classroom_id' => $group->id, 'first_name' => 'Ana']);
    $beto = Child::factory()->create(['classroom_id' => $group->id, 'first_name' => 'Beto']);

    return [$group, $subject, $ana, $beto];
}

it('promedia las notas con redondeo a un decimal', function () {
    expect(PeriodGrades::round((2.9 + 3.0) / 2))->toBe(3.0)
        ->and(PeriodGrades::round((4.0 + 4.5 + 4.6) / 3))->toBe(4.4);
});

it('el docente ve en Notas solo sus materias', function () {
    [$group] = teachingSetup();
    $other = Classroom::factory()->create(['name' => 'Grupo ajeno', 'level' => GradeLevel::Cuarto]);
    $other->subjects()->attach(Subject::where('name', 'Inglés')->first()->id, ['educator_id' => User::factory()->educator()->create()->id]);

    $this->actingAs($this->teacher)->get(route('educator.grades.index'))
        ->assertOk()->assertSee($group->name)->assertSee('Matemáticas')->assertDontSee('Grupo ajeno');
});

it('registra actividades y notas, y calcula promedio y desempeño', function () {
    [$group, $math, $ana, $beto] = teachingSetup();
    $this->actingAs($this->teacher);

    $this->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz 1'])->assertSessionHasNoErrors();
    $this->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Taller'])->assertSessionHasNoErrors();
    [$quiz, $workshop] = Assessment::orderBy('id')->get();

    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [
        $quiz->id => [$ana->id => '4.0', $beto->id => '2.5'],
        $workshop->id => [$ana->id => '5.0', $beto->id => ''],
    ]])->assertRedirect(route('educator.grades.show', [$group, $math, 'periodo' => 1]));

    expect(AssessmentScore::count())->toBe(3);

    $this->get(route('educator.grades.show', [$group, $math, 'periodo' => 1]))
        ->assertOk()
        ->assertSeeInOrder(['Ana', '4.5', 'Alto'])   // (4.0 + 5.0) / 2
        ->assertSeeInOrder(['Beto', '2.5', 'Bajo']);  // la casilla vacía no cuenta
});

it('por defecto abre el periodo que corresponde a hoy', function () {
    [$group, $math] = teachingSetup();
    Carbon::setTestNow('2027-07-15');

    $this->actingAs($this->teacher)->get(route('educator.grades.show', [$group, $math]))
        ->assertSee('name="periodo" value="2"', false);
});

it('vaciar una casilla borra la nota', function () {
    [$group, $math, $ana] = teachingSetup();
    $this->actingAs($this->teacher)->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz']);
    $quiz = Assessment::first();

    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [$quiz->id => [$ana->id => '3.0']]]);
    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [$quiz->id => [$ana->id => '']]]);

    expect(AssessmentScore::count())->toBe(0);
});

it('rechaza notas fuera de 1.0 a 5.0 o con dos decimales', function (string $value) {
    [$group, $math, $ana] = teachingSetup();
    $this->actingAs($this->teacher)->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz']);
    $quiz = Assessment::first();

    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [$quiz->id => [$ana->id => $value]]])
        ->assertSessionHasErrors();

    expect(AssessmentScore::count())->toBe(0);
})->with(['0.5', '5.5', '3.25', 'abc']);

it('ignora estudiantes de otro grupo metidos a mano en el formulario', function () {
    [$group, $math] = teachingSetup();
    $intruder = Child::factory()->create(['classroom_id' => Classroom::factory()->create()->id]);
    $this->actingAs($this->teacher)->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz']);
    $quiz = Assessment::first();

    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [$quiz->id => [$intruder->id => '5.0']]]);

    expect(AssessmentScore::count())->toBe(0);
});

it('quitar una actividad borra sus notas', function () {
    [$group, $math, $ana] = teachingSetup();
    $this->actingAs($this->teacher)->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz']);
    $quiz = Assessment::first();
    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [$quiz->id => [$ana->id => '4.0']]]);

    $this->delete(route('educator.grades.assessments.destroy', [$group, $math, $quiz]), ['periodo' => 1]);

    expect(Assessment::count())->toBe(0)->and(AssessmentScore::count())->toBe(0);
});

it('un docente que no dicta la materia no puede ver ni poner notas', function () {
    [$group, $math] = teachingSetup();
    $english = Subject::where('name', 'Inglés')->first();
    $group->subjects()->attach($english->id, ['educator_id' => User::factory()->educator()->create()->id]);
    $this->actingAs($this->teacher);

    $this->get(route('educator.grades.show', [$group, $english]))->assertForbidden();
    $this->post(route('educator.grades.assessments.store', [$group, $english]), ['periodo' => 1, 'title' => 'X'])->assertForbidden();

    expect(Assessment::count())->toBe(0);
});

it('un periodo cerrado se puede consultar pero no editar', function () {
    [$group, $math, $ana] = teachingSetup();
    $this->actingAs($this->teacher)->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Quiz']);
    SchoolYear::current()->periods()->where('number', 1)->update(['closed_at' => now()]);

    $this->get(route('educator.grades.show', [$group, $math, 'periodo' => 1]))->assertOk()->assertSee('Este periodo está cerrado');
    $this->post(route('educator.grades.assessments.store', [$group, $math]), ['periodo' => 1, 'title' => 'Otro'])->assertForbidden();
    $this->put(route('educator.grades.scores', [$group, $math]), ['periodo' => 1, 'scores' => [Assessment::first()->id => [$ana->id => '4.0']]])->assertForbidden();
});

it('sin año escolar avisa que coordinación debe crearlo', function () {
    [$group, $math] = teachingSetup();
    SchoolYear::query()->delete();
    DB::table('academic_periods')->delete();

    $this->actingAs($this->teacher)->get(route('educator.grades.show', [$group, $math]))->assertOk()->assertSee('Todavía no hay año escolar');
});

it('en preescolar escribe un informe por niño y dimensión, cifrado', function () {
    [$group, $dimension, $ana, $beto] = teachingSetup(GradeLevel::Jardin, 'Comunicativa');
    $this->actingAs($this->teacher);

    $this->get(route('educator.grades.show', [$group, $dimension]))->assertOk()->assertSee('Guardar informes');

    $this->put(route('educator.grades.reports', [$group, $dimension]), ['periodo' => 1, 'reports' => [
        $ana->id => 'Narra cuentos cortos con secuencia.', $beto->id => '  ',
    ]])->assertSessionHasNoErrors();

    $report = DimensionReport::sole();
    expect($report->child_id)->toBe($ana->id)
        ->and($report->body)->toBe('Narra cuentos cortos con secuencia.')
        ->and(DB::table('dimension_reports')->value('body'))->not->toContain('Narra'); // cifrado en la BD

    $this->get(route('educator.grades.show', [$group, $dimension, 'periodo' => 1]))->assertSee('Narra cuentos cortos con secuencia.');
});

it('en primaria Notas está en la barra; en preescolar se entra desde Grupos', function () {
    [$group, $dimension] = teachingSetup(GradeLevel::Jardin, 'Cognitiva');
    $this->actingAs($this->teacher)->get(route('educator.groups.index'))
        ->assertSee('Notas e informes')->assertSee(route('educator.grades.show', [$group, $dimension]));

    $this->actingAs(User::factory()->educator()->create(['school_id' => School::factory()->type(\App\Enums\SchoolType::Primary)->create()->id]))
        ->get(route('educator.groups.index'))->assertSee(route('educator.grades.index'));
});
