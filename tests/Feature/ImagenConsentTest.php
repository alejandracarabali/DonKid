<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

// RF-67: autorización de uso de imagen, por niño y revocable.

function guardianWithChild(): array
{
    $guardian = User::factory()->guardian()->create();
    $child = Child::factory()->create(['first_name' => 'Zoe']);
    $guardian->children()->attach($child);

    return [$guardian, $child];
}

it('el Inicio pregunta hasta que la familia responda, y "No" también cuenta', function () {
    [$guardian, $child] = guardianWithChild();

    $this->actingAs($guardian)->get(route('guardian.dashboard'))->assertSee('¿Autorizas fotos de Zoe?');

    $this->from(route('guardian.dashboard'))
        ->put(route('guardian.children.image-consent', $child), ['consent' => 0])
        ->assertRedirect(route('guardian.dashboard'));

    expect($child->fresh()->hasImageConsent())->toBeFalse();
    $this->get(route('guardian.dashboard'))->assertDontSee('¿Autorizas fotos de Zoe?');
});

it('autorizar guarda cuándo, quién y desde qué IP; retirar lo borra', function () {
    [$guardian, $child] = guardianWithChild();

    $this->actingAs($guardian)->put(route('guardian.children.image-consent', $child), ['consent' => 1]);
    $child->refresh();
    expect($child->hasImageConsent())->toBeTrue()
        ->and($child->image_consent_by)->toBe($guardian->id)
        ->and($child->image_consent_ip)->toBe('127.0.0.1');

    $this->put(route('guardian.children.image-consent', $child), ['consent' => 0]);
    expect($child->fresh())->hasImageConsent()->toBeFalse()->image_consent_by->toBeNull();
});

it('una familia no puede autorizar por el hijo de otra', function () {
    [, $child] = guardianWithChild();

    $this->actingAs(User::factory()->guardian()->create())
        ->put(route('guardian.children.image-consent', $child), ['consent' => 1])
        ->assertForbidden();

    expect($child->fresh()->hasImageConsent())->toBeFalse();
});

it('la casilla de la inscripción pasa al niño al aprobarla', function (bool $checked) {
    Storage::fake('local');
    $school = \App\Models\School::query()->orderBy('id')->first();
    $this->post(route('enrollment.store', $school), enrollmentData(['consent_image' => $checked ? '1' : null]))->assertSessionHasNoErrors();
    $application = \App\Models\EnrollmentApplication::withoutGlobalScopes()->latest('id')->first();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.enrollments.approve', $application), ['classroom_id' => Classroom::factory()->create()->id]);

    $child = Child::where('first_name', 'Sofía')->sole();
    expect($child->hasImageConsent())->toBe($checked)
        ->and($child->awaitsImageConsentAnswer())->toBeFalse(); // ya respondió en el formulario
})->with(['marcada' => true, 'sin marcar' => false]);