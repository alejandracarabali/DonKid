<?php

use App\Jobs\NotifyGuardiansOfAnnouncement;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use App\Notifications\NewAnnouncement;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;

function notifSetup(): array
{
    $grupo = Classroom::create(['name' => 'Caminadores']);
    $otro = Classroom::create(['name' => 'Exploradores']);
    $educator = User::factory()->educator()->create();
    $educator->classrooms()->attach($grupo);

    $madre = User::factory()->guardian()->create();
    Child::create(['classroom_id' => $grupo->id, 'first_name' => 'Ana', 'last_name' => 'P', 'birth_date' => '2023-01-01'])
        ->guardians()->attach($madre, ['relationship' => 'Mamá']);

    $ajena = User::factory()->guardian()->create();
    Child::create(['classroom_id' => $otro->id, 'first_name' => 'Leo', 'last_name' => 'G', 'birth_date' => '2023-01-01'])
        ->guardians()->attach($ajena, ['relationship' => 'Papá']);

    return [$grupo, $educator, $madre, $ajena];
}

it('despacha el job al publicar un aviso', function () {
    Bus::fake();
    [$grupo, $educator] = notifSetup();

    $this->actingAs($educator)->post(route('educator.announcements.store'), [
        'classroom_id' => $grupo->id, 'title' => 'Paseo', 'body' => 'Mañana.',
    ]);

    Bus::assertDispatched(NotifyGuardiansOfAnnouncement::class);
});

it('notifica solo a las familias del grupo', function () {
    Notification::fake();
    [$grupo, $educator, $madre, $ajena] = notifSetup();

    $this->actingAs($educator)->post(route('educator.announcements.store'), [
        'classroom_id' => $grupo->id, 'title' => 'Paseo', 'body' => 'Mañana.',
    ]);

    Notification::assertSentTo($madre, NewAnnouncement::class);
    Notification::assertNotSentTo($ajena, NewAnnouncement::class);
});