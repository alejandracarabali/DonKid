<?php

use App\Models\Activity;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\School;
use App\Models\User;

// Grupo de OTRO colegio.
function classroomInAnotherSchool(): Classroom
{
    return Classroom::factory()->create(['school_id' => School::factory()->create()->id]);
}

// Actividad en un grupo de OTRO colegio.
function activityInAnotherSchool(): Activity
{
    $activity = new Activity(['title' => 'Ajena', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $activity->classroom_id = classroomInAnotherSchool()->id;
    $activity->save();

    return $activity;
}

test('coordinación recibe 404 al editar una actividad de otro colegio', function () {
    $ajena = activityInAnotherSchool();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('educator.activities.edit', $ajena))
        ->assertNotFound();
});

test('coordinación no puede borrar una actividad de otro colegio', function () {
    $ajena = activityInAnotherSchool();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('educator.activities.destroy', $ajena))
        ->assertNotFound();

    expect(Activity::withoutGlobalScopes()->whereKey($ajena->id)->exists())->toBeTrue();
});

test('las bitácoras solo se consultan dentro del colegio', function () {
    $propio = Child::factory()->create();
    $ajeno = Child::factory()->create(['classroom_id' => classroomInAnotherSchool()->id]);
    DailyReport::forceCreate(['child_id' => $propio->id, 'report_date' => today()]);
    DailyReport::forceCreate(['child_id' => $ajeno->id, 'report_date' => today()]);

    $this->actingAs(User::factory()->admin()->create());

    expect(DailyReport::count())->toBe(1)
        ->and(DailyReport::first()->child_id)->toBe($propio->id);
});

test('sin usuario conectado se ven todas las actividades (colas y consola)', function () {
    activityInAnotherSchool();
    $propia = new Activity(['title' => 'Propia', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $propia->classroom_id = Classroom::factory()->create()->id;
    $propia->save();

    expect(Activity::count())->toBe(2);
});