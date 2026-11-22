<?php

use App\Enums\GradeLevel;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\EnrollmentRenewal;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// RF-36: renovación de matrícula.

function openRenewal(int $year = 2027): School
{
    $school = School::query()->orderBy('id')->first();
    $school->forceFill(['renewal_year' => $year])->save();

    return $school;
}

// Familia con un hijo en un grupo del nivel indicado.
function familyWithChild(GradeLevel $level = GradeLevel::Jardin): array
{
    $grupo = Classroom::factory()->level($level)->create(['name' => 'Grupo '.$level->value]);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ana']);
    $madre = User::factory()->guardian()->create();
    $madre->children()->attach($nino, ['relationship' => 'Madre']);

    return [$madre, $nino];
}

function renewalData(array $overrides = []): array
{
    return array_merge([
        'next_level' => 'transicion',
        'guardian_phone' => '300 999 8877',
        'guardian_address' => 'Carrera 5 # 10-20, Cali',
        'health' => ['eps' => 'Sanitas', 'conditions' => 'Asma leve'],
        'consent_data' => '1',
        'consent_health' => '1',
    ], $overrides);
}

it('coordinación abre y cierra la renovación', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.renewals.open'), ['year' => now()->year + 1])
        ->assertRedirect(route('admin.renewals.index'));
    expect($admin->school->fresh()->renewal_year)->toBe(now()->year + 1);

    $this->actingAs($admin)->post(route('admin.renewals.close'));
    expect($admin->school->fresh()->renewal_year)->toBeNull();
});

it('solo coordinación abre la renovación', function () {
    $this->actingAs(User::factory()->guardian()->create())->post(route('admin.renewals.open'), ['year' => 2027])->assertForbidden();
    $this->actingAs(User::factory()->educator()->create())->post(route('admin.renewals.open'), ['year' => 2027])->assertForbidden();
});

it('la familia ve el botón solo cuando la renovación está abierta', function () {
    [$madre] = familyWithChild();

    $this->actingAs($madre)->get(route('guardian.dashboard'))->assertDontSee('Renovar matrícula');

    openRenewal(2027);
    // fresh(): el usuario del test guardó en memoria el colegio de la petición anterior.
    $this->actingAs($madre->fresh())->get(route('guardian.dashboard'))->assertSee('Renovar matrícula 2027');
});

it('prellena el formulario con el nivel siguiente y la ficha de salud', function () {
    openRenewal();
    [$madre, $nino] = familyWithChild(GradeLevel::Jardin);
    $nino->healthRecord()->create(['eps' => 'Sura', 'conditions' => 'Alergia al polvo']);

    $this->actingAs($madre)->get(route('guardian.renewals.create', $nino))
        ->assertOk()
        ->assertSee('value="transicion" selected', false)
        ->assertSee('Sura')
        ->assertSee('Alergia al polvo');
});

it('renueva, guarda la prueba de autorización y actualiza la ficha de salud', function () {
    openRenewal(2027);
    [$madre, $nino] = familyWithChild();

    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData())
        ->assertRedirect(route('guardian.dashboard'));

    $renovacion = EnrollmentRenewal::first();

    expect($renovacion->year)->toBe(2027)
        ->and($renovacion->next_level)->toBe(GradeLevel::Transicion)
        ->and($renovacion->guardian_id)->toBe($madre->id)
        ->and($renovacion->data_consent_at)->not->toBeNull()
        ->and($renovacion->consent_policy_version)->toBe(config('privacy.policy_version'))
        ->and($nino->fresh()->healthRecord->eps)->toBe('Sanitas')
        ->and(DB::table('enrollment_renewals')->value('guardian_address'))->not->toContain('Carrera 5');

    $this->actingAs($madre)->get(route('guardian.dashboard'))->assertSee('Matrícula 2027 renovada');
});

it('no deja renovar dos veces el mismo año', function () {
    openRenewal();
    [$madre, $nino] = familyWithChild();

    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData());
    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData())->assertStatus(409);
    $this->actingAs($madre)->get(route('guardian.renewals.create', $nino))->assertRedirect(route('guardian.dashboard'));

    expect(EnrollmentRenewal::count())->toBe(1);
});

it('no deja renovar al hijo de otra familia', function () {
    openRenewal();
    [, $nino] = familyWithChild();
    $otra = User::factory()->guardian()->create();
    $otra->children()->attach(Child::factory()->create());

    $this->actingAs($otra)->post(route('guardian.renewals.store', $nino), renewalData())->assertForbidden();
});

it('responde 404 si la renovación está cerrada', function () {
    [$madre, $nino] = familyWithChild();

    $this->actingAs($madre)->get(route('guardian.renewals.create', $nino))->assertNotFound();
});

it('quinto no renueva porque termina primaria', function () {
    openRenewal();
    [$madre, $nino] = familyWithChild(GradeLevel::Quinto);

    $this->actingAs($madre)->get(route('guardian.renewals.create', $nino))->assertNotFound();
    $this->actingAs($madre)->get(route('guardian.dashboard'))->assertDontSee('Renovar matrícula');
});

it('no guarda datos de salud sin la autorización de salud', function () {
    config(['privacy.health_documents_required' => false]);
    openRenewal();
    [$madre, $nino] = familyWithChild();

    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData(['consent_health' => null]))
        ->assertSessionHasErrors('health.eps');

    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData(['consent_health' => null, 'health' => []]))
        ->assertSessionHasNoErrors();

    expect($nino->fresh()->healthRecord)->toBeNull();
});

it('coordinación ve quién renovó y quién está pendiente', function () {
    openRenewal(2027);
    [$madre, $nino] = familyWithChild();
    Child::factory()->create(['first_name' => 'Beto']);
    $this->actingAs($madre)->post(route('guardian.renewals.store', $nino), renewalData());

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.renewals.index'))
        ->assertOk()
        ->assertSee('1 de 2 renovados')
        ->assertSee('Transición');
});
