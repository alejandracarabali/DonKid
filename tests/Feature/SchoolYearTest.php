<?php

use App\Models\AcademicPeriod;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Carbon;

// RF-02: año escolar y periodos académicos.

afterEach(fn () => Carbon::setTestNow());

function createYear(User $admin, int $year = 2027, string $starts = '2027-01-26', string $ends = '2027-11-26'): SchoolYear
{
    test()->actingAs($admin)->post(route('admin.school-years.store'), [
        'year' => $year, 'starts_on' => $starts, 'ends_on' => $ends,
    ])->assertSessionHasNoErrors();

    return SchoolYear::where('year', $year)->first();
}

it('crea el año escolar con 3 periodos seguidos que cubren todo el año', function () {
    $year = createYear(User::factory()->admin()->create());
    $periods = $year->periods;

    expect($periods)->toHaveCount(3)
        ->and($periods->pluck('number')->all())->toBe([1, 2, 3])
        ->and($periods->first()->starts_on->toDateString())->toBe('2027-01-26')
        ->and($periods->last()->ends_on->toDateString())->toBe('2027-11-26');

    // Sin huecos ni cruces: cada periodo empieza el día después de que termina el anterior.
    foreach ([1, 2] as $i) {
        expect($periods[$i]->starts_on->toDateString())->toBe($periods[$i - 1]->ends_on->copy()->addDay()->toDateString());
    }
});

it('el primer año creado queda como año actual', function () {
    $admin = User::factory()->admin()->create();
    $first = createYear($admin, 2027);
    $second = createYear($admin, 2028, '2028-01-24', '2028-11-24');

    expect($first->fresh()->is_current)->toBeTrue()
        ->and($second->fresh()->is_current)->toBeFalse();
});

it('marcar otro año como actual desmarca el anterior', function () {
    $admin = User::factory()->admin()->create();
    $first = createYear($admin, 2027);
    $second = createYear($admin, 2028, '2028-01-24', '2028-11-24');

    $this->actingAs($admin)->post(route('admin.school-years.current', $second));

    expect($first->fresh()->is_current)->toBeFalse()
        ->and($second->fresh()->is_current)->toBeTrue();
});

it('no permite dos años iguales en el mismo colegio, pero sí en colegios distintos', function () {
    $admin = User::factory()->admin()->create();
    createYear($admin, 2027);

    $this->actingAs($admin)->post(route('admin.school-years.store'), [
        'year' => 2027, 'starts_on' => '2027-01-26', 'ends_on' => '2027-11-26',
    ])->assertSessionHasErrors('year');

    $otro = User::factory()->admin()->create(['school_id' => School::factory()->create()->id]);
    createYear($otro, 2027);
});

it('ajusta las fechas de los periodos', function () {
    $admin = User::factory()->admin()->create();
    $year = createYear($admin);

    $this->actingAs($admin)->put(route('admin.school-years.update', $year), ['periods' => [
        1 => ['starts_on' => '2027-01-26', 'ends_on' => '2027-04-16', 'grades_due_on' => '2027-04-23'],
        2 => ['starts_on' => '2027-04-19', 'ends_on' => '2027-08-13', 'grades_due_on' => '2027-08-20'],
        3 => ['starts_on' => '2027-08-17', 'ends_on' => '2027-11-26', 'grades_due_on' => '2027-11-26'],
    ]])->assertRedirect(route('admin.school-years.index'));

    expect($year->fresh()->periods[0]->grades_due_on->toDateString())->toBe('2027-04-23');
});

it('rechaza periodos que se cruzan o se salen del año', function () {
    $admin = User::factory()->admin()->create();
    $year = createYear($admin);

    $this->actingAs($admin)->put(route('admin.school-years.update', $year), ['periods' => [
        1 => ['starts_on' => '2027-01-26', 'ends_on' => '2027-05-01', 'grades_due_on' => '2027-05-01'],
        2 => ['starts_on' => '2027-04-20', 'ends_on' => '2027-08-13', 'grades_due_on' => '2027-08-13'],
        3 => ['starts_on' => '2027-08-17', 'ends_on' => '2027-12-20', 'grades_due_on' => '2027-12-20'],
    ]])->assertSessionHasErrors(['periods.2.starts_on', 'periods.3.starts_on']);
});

it('sabe qué periodo está en curso', function () {
    $year = createYear(User::factory()->admin()->create());
    $year->load('periods');

    expect($year->periodOn(Carbon::parse('2027-02-10'))?->number)->toBe(1)
        ->and($year->periodOn(Carbon::parse('2027-11-20'))?->number)->toBe(3)
        ->and($year->periodOn(Carbon::parse('2027-12-20')))->toBeNull()
        ->and($year->periods[1]->name())->toBe('Segundo periodo');
});

it('el inicio de coordinación muestra el periodo en curso', function () {
    Carbon::setTestNow('2027-02-10 09:00');
    $admin = User::factory()->admin()->create();
    createYear($admin);

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()->assertSee('Primer periodo 2027');
});

it('sin año escolar, el inicio invita a crearlo', function () {
    $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))
        ->assertSee('Aún no hay año escolar');
});

it('coordinación no ve ni edita años escolares de otro colegio', function () {
    $year = createYear(User::factory()->admin()->create());
    $otro = User::factory()->admin()->create(['school_id' => School::factory()->create()->id]);

    $this->actingAs($otro)->get(route('admin.school-years.edit', $year))->assertNotFound();
    $this->actingAs($otro)->get(route('admin.school-years.index'))->assertDontSee('2027');
});

it('solo coordinación administra el año escolar', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get(route('admin.school-years.index'))->assertForbidden();
})->with(['educator', 'guardian']);
