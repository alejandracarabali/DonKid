<?php

use App\Models\Activity;
use App\Models\ActivityPhoto;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// RF-32: registro de actividades hechas, con nota y fotos.

beforeEach(fn () => Storage::fake('local'));

/** @return array{0: Classroom, 1: User, 2: Activity} */
function recordSetup(): array
{
    $classroom = Classroom::factory()->create();
    $educator = User::factory()->educator()->create();
    $educator->classrooms()->attach($classroom);
    $activity = new Activity(['title' => 'Pintura con témperas', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $activity->classroom_id = $classroom->id;
    $activity->save();

    return [$classroom, $educator, $activity];
}

function photo(string $name = 'foto.jpg'): UploadedFile
{
    return UploadedFile::fake()->image($name, 800, 600);
}

it('la docente marca la actividad como hecha con nota y fotos', function () {
    [, $educator, $activity] = recordSetup();

    $this->actingAs($educator)
        ->put(route('educator.activities.record.update', $activity), ['done_note' => 'Hicieron un mural.', 'photos' => [photo(), photo('b.png')]])
        ->assertSessionHasNoErrors();

    $activity->refresh();
    expect($activity->isDone())->toBeTrue()
        ->and($activity->done_by)->toBe($educator->id)
        ->and($activity->done_note)->toBe('Hicieron un mural.')
        ->and($activity->photos)->toHaveCount(2);
    Storage::disk('local')->assertExists($activity->photos->first()->path);
});

it('avisa a la docente qué niños no tienen autorización de imagen', function () {
    [$classroom, $educator, $activity] = recordSetup();
    Child::factory()->create(['classroom_id' => $classroom->id, 'first_name' => 'Zuleima']);
    $ok = Child::factory()->create(['classroom_id' => $classroom->id, 'first_name' => 'Ximena']);
    $ok->forceFill(['image_consent_at' => now()])->save();

    $this->actingAs($educator)->get(route('educator.activities.record', $activity))
        ->assertOk()->assertSee('No deben aparecer en las fotos')->assertSee('Zuleima')->assertDontSee('Ximena');
});

it('no acepta más de 10 fotos ni archivos que no son imágenes', function () {
    [, $educator, $activity] = recordSetup();
    $this->actingAs($educator);

    $this->put(route('educator.activities.record.update', $activity), ['photos' => array_map(fn ($i) => photo("$i.jpg"), range(1, 11))])
        ->assertSessionHasErrors('photos');
    $this->put(route('educator.activities.record.update', $activity), ['photos' => [UploadedFile::fake()->create('x.pdf', 10, 'application/pdf')]])
        ->assertSessionHasErrors('photos.0');

    expect(ActivityPhoto::count())->toBe(0);
});

it('quitar una foto borra el archivo, y eliminar la actividad borra todas', function () {
    [, $educator, $activity] = recordSetup();
    $this->actingAs($educator)->put(route('educator.activities.record.update', $activity), ['photos' => [photo(), photo('b.jpg')]]);
    [$first, $second] = $activity->photos()->get();

    $this->delete(route('educator.activities.photos.destroy', [$activity, $first]));
    Storage::disk('local')->assertMissing($first->path);

    $this->delete(route('educator.activities.destroy', $activity));
    Storage::disk('local')->assertMissing($second->path);
});

it('solo ven las fotos las familias del grupo que autorizaron', function () {
    [$classroom, $educator, $activity] = recordSetup();
    $this->actingAs($educator)->put(route('educator.activities.record.update', $activity), ['photos' => [photo()]]);
    $photo = $activity->photos()->first();
    $url = route('activities.photos.show', [$activity, $photo]);

    $withConsent = User::factory()->guardian()->create();
    $child = Child::factory()->create(['classroom_id' => $classroom->id]);
    $child->forceFill(['image_consent_at' => now()])->save();
    $withConsent->children()->attach($child);

    $withoutConsent = User::factory()->guardian()->create();
    $withoutConsent->children()->attach(Child::factory()->create(['classroom_id' => $classroom->id]));

    $otherGroup = User::factory()->guardian()->create();
    $other = Child::factory()->create();
    $other->forceFill(['image_consent_at' => now()])->save();
    $otherGroup->children()->attach($other);

    $this->actingAs($educator)->get($url)->assertOk();
    $this->actingAs($withConsent)->get($url)->assertOk();
    $this->actingAs($withoutConsent)->get($url)->assertForbidden();
    $this->actingAs($otherGroup)->get($url)->assertForbidden();
});

it('otra docente no puede registrar actividades de un grupo ajeno', function () {
    [, , $activity] = recordSetup();

    $this->actingAs(User::factory()->educator()->create())
        ->put(route('educator.activities.record.update', $activity), ['done_note' => 'x'])
        ->assertForbidden();

    expect($activity->fresh()->isDone())->toBeFalse();
});

it('la familia ve la nota siempre, y las fotos solo si autorizó', function () {
    [$classroom, $educator, $activity] = recordSetup();
    $this->actingAs($educator)->put(route('educator.activities.record.update', $activity), ['done_note' => 'Mural colectivo', 'photos' => [photo()]]);
    $photoUrl = route('activities.photos.show', [$activity, $activity->photos()->first()]);

    $guardian = User::factory()->guardian()->create();
    $child = Child::factory()->create(['classroom_id' => $classroom->id]);
    $guardian->children()->attach($child);
    $week = route('guardian.activities.index', ['semana' => now()->toDateString()]);

    $this->actingAs($guardian)->get($week)
        ->assertSee('Mural colectivo')->assertDontSee($photoUrl)->assertSee('autoriza el uso de imagen');

    $child->forceFill(['image_consent_at' => now()])->save();
    $this->get($week)->assertSee($photoUrl);
});

it('coordinación ve el registro con resumen y filtros', function () {
    [$classroom, $educator, $activity] = recordSetup();
    $this->actingAs($educator)->put(route('educator.activities.record.update', $activity), ['done_note' => 'Mural colectivo', 'photos' => [photo()]]);

    $other = new Activity(['title' => 'Cuento leído', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $other->classroom_id = Classroom::factory()->create()->id;
    $other->forceFill(['done_at' => now()])->save();

    $pending = new Activity(['title' => 'Aún no se hace', 'starts_at' => now(), 'ends_at' => now()->addHour()]);
    $pending->classroom_id = $classroom->id;
    $pending->save();

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->get(route('admin.activity-records.index'))
        ->assertOk()
        ->assertSee('Mural colectivo')->assertSee('Cuento leído')->assertDontSee('Aún no se hace')
        ->assertSee('Sin nota de registro')
        ->assertSeeInOrder(['Hechas', '2', 'Con fotos', '1', 'Grupos', '2']);

    $this->get(route('admin.activity-records.index', ['grupo' => $classroom->id]))
        ->assertSee('Mural colectivo')->assertDontSee('Cuento leído');

    $this->get(route('activities.photos.show', [$activity, $activity->photos()->first()]))->assertOk();
});

it('docentes y familias no entran al registro de coordinación', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get(route('admin.activity-records.index'))->assertForbidden();
})->with(['educator', 'guardian']);