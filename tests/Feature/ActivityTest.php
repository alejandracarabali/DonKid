<?php

use App\Models\Child;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\User;

it('rechaza una actividad que termina antes de empezar', function () {
    $educator = User::factory()->educator()->create();
    $classroom = Classroom::factory()->create();
    $educator->classrooms()->attach($classroom);

    $this->actingAs($educator)
        ->post(route('educator.activities.store'), [
            'classroom_id' => $classroom->id,
            'title' => 'Reunión',
            'starts_at' => '2026-11-05T10:00',
            'ends_at' => '2026-11-05T09:00',
        ])
        ->assertSessionHasErrors('ends_at');

    expect(Activity::count())->toBe(0);
});

it('rechaza editar una actividad para que termine antes de empezar', function () {
    $educator = User::factory()->educator()->create();
    $classroom = Classroom::factory()->create();
    $educator->classrooms()->attach($classroom);

    $activity = new Activity([
        'title' => 'Reunión',
        'starts_at' => '2026-11-05 10:00',
        'ends_at' => '2026-11-05 11:00',
    ]);
    $activity->classroom_id = $classroom->id;
    $activity->author_id = $educator->id;
    $activity->save();

    $this->actingAs($educator)
        ->put(route('educator.activities.update', $activity), [
            'classroom_id' => $classroom->id,
            'title' => 'Reunión',
            'starts_at' => '2026-11-05T10:00',
            'ends_at' => '2026-11-05T09:00',
        ])
        ->assertSessionHasErrors('ends_at');
});

it('muestra a la familia solo las actividades de los grupos de sus hijos', function () {
    $guardian = User::factory()->guardian()->create();
    $mine = Classroom::factory()->create();
    $other = Classroom::factory()->create();
    $child = Child::factory()->create(['classroom_id' => $mine->id]);
    $guardian->children()->attach($child);

    foreach ([[$mine, 'Cuento en círculo'], [$other, 'Actividad ajena']] as [$room, $title]) {
        $a = new Activity(['title' => $title, 'starts_at' => now()->setTime(9, 0), 'ends_at' => now()->setTime(10, 0)]);
        $a->classroom_id = $room->id;
        $a->save();
    }

    $this->actingAs($guardian)
        ->get(route('guardian.activities.index'))
        ->assertOk()
        ->assertSee('Cuento en círculo')
        ->assertDontSee('Actividad ajena');
});

it('impide que una familia entre a la gestión de actividades', function () {
    $guardian = User::factory()->guardian()->create();

    $this->actingAs($guardian)->get(route('educator.activities.index'))->assertForbidden();
});

it('impide que una educadora cree actividades en un grupo ajeno', function () {
    $educator = User::factory()->educator()->create();
    $own = Classroom::factory()->create();
    $foreign = Classroom::factory()->create();
    $educator->classrooms()->attach($own);

    $this->actingAs($educator)
        ->post(route('educator.activities.store'), [
            'classroom_id' => $foreign->id,
            'title' => 'Intruso',
            'starts_at' => '2026-11-09T09:00',
            'ends_at' => '2026-11-09T10:00',
        ])
        ->assertForbidden();

    expect(Activity::count())->toBe(0);
});

it('impide que una educadora edite o borre actividades de otro grupo', function () {
    $educator = User::factory()->educator()->create();
    $educator->classrooms()->attach(Classroom::factory()->create());
    $foreign = Classroom::factory()->create();

    $activity = new Activity(['title' => 'Ajena', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $activity->classroom_id = $foreign->id;
    $activity->save();

    $this->actingAs($educator)->get(route('educator.activities.edit', $activity))->assertForbidden();
    $this->actingAs($educator)->delete(route('educator.activities.destroy', $activity))->assertForbidden();

    expect(Activity::count())->toBe(1);
});