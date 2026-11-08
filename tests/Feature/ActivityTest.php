<?php

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