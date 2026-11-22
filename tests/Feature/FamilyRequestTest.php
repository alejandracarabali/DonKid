<?php

use App\Enums\FamilyRequestStatus;
use App\Enums\FamilyRequestType;
use App\Enums\GradeLevel;
use App\Enums\SchoolType;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\FamilyRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Fase 5: solicitudes de la familia (excusa, permiso con código, recomendación).

function requestSetup(): array
{
    $grupo = Classroom::factory()->create();
    $director = User::factory()->educator()->create();
    $materia = User::factory()->educator()->create();
    $grupo->educators()->attach([$director->id, $materia->id]);
    $grupo->update(['director_id' => $director->id]);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ana']);
    $madre = User::factory()->guardian()->create(['name' => 'Marta']);
    $madre->children()->attach($nino);

    return [$madre, $nino, $director, $materia];
}

it('la familia envía una excusa', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'excuse', 'date' => today()->toDateString(), 'reason' => 'Cita médica', 'note' => 'Llega a las 10:00',
    ])->assertRedirect(route('guardian.requests.child', $nino));

    $solicitud = FamilyRequest::first();

    expect($solicitud->type)->toBe(FamilyRequestType::Excuse)
        ->and($solicitud->status)->toBe(FamilyRequestStatus::Sent)
        ->and($solicitud->summary())->toBe('Excusa: Cita médica')
        ->and($solicitud->guardian_id)->toBe($madre->id);
});

it('un permiso para que recoja otra persona genera un código de 4 dígitos', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'permission', 'date' => today()->toDateString(), 'reason' => 'other_person',
        'pickup_name' => 'Carlos Ríos', 'pickup_relationship' => 'Tío/a', 'pickup_phone' => '300 123 4567',
    ])->assertSessionHas('pickup_code');

    $solicitud = FamilyRequest::first();

    expect($solicitud->pickup_code)->toMatch('/^\d{4}$/')
        ->and($solicitud->summary())->toBe('Recoge Carlos Ríos (tío/a)');

    $this->actingAs($madre)->get(route('guardian.requests.child', $nino))->assertSee($solicitud->pickup_code);
});

it('el permiso con otra persona exige nombre, parentesco y teléfono', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'permission', 'date' => today()->toDateString(), 'reason' => 'other_person',
    ])->assertSessionHasErrors(['pickup_name', 'pickup_relationship', 'pickup_phone']);
});

it('una salida anticipada no lleva datos de recogida ni código', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'permission', 'date' => today()->toDateString(), 'reason' => 'early_exit', 'time' => '11:30',
    ])->assertSessionHasNoErrors();

    expect(FamilyRequest::first()->pickup_code)->toBeNull();
});

it('un permiso no puede ser para un día pasado', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'permission', 'date' => now()->subDay()->toDateString(), 'reason' => 'early_exit',
    ])->assertSessionHasErrors('date');
});

it('la recomendación exige el texto y lo guarda cifrado', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation'])
        ->assertSessionHasErrors('note');

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'recommendation', 'note' => 'Hoy tiene tos',
    ])->assertSessionHasNoErrors();

    expect(DB::table('family_requests')->value('note'))->not->toContain('tos');
});

it('el director ve el bloque en la Bitácora y confirma el permiso', function () {
    [$madre, $nino, $director] = requestSetup();
    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'permission', 'date' => today()->toDateString(), 'reason' => 'other_person',
        'pickup_name' => 'Carlos Ríos', 'pickup_relationship' => 'Tío/a', 'pickup_phone' => '3001234567',
    ]);
    $solicitud = FamilyRequest::first();

    $this->actingAs($director)->get(route('educator.reports.index'))
        ->assertSee('Solicitudes de familias')
        ->assertSee('Recoge Carlos Ríos')
        ->assertSee('Código '.$solicitud->pickup_code)
        ->assertSee('Confirmar');

    $this->actingAs($director)->post(route('educator.requests.handle', $solicitud))->assertRedirect();

    expect($solicitud->fresh()->status)->toBe(FamilyRequestStatus::Confirmed)
        ->and($solicitud->fresh()->handled_by)->toBe($director->id);

    $this->actingAs($madre)->get(route('guardian.requests.child', $nino))->assertSee('Confirmada');
    $this->actingAs($director)->get(route('educator.reports.index'))->assertDontSee('Solicitudes de familias');
});

it('una excusa o recomendación se marca como vista', function () {
    [$madre, $nino, $director] = requestSetup();
    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation', 'note' => 'Tos']);

    $this->actingAs($director)->post(route('educator.requests.handle', FamilyRequest::first()));

    expect(FamilyRequest::first()->status)->toBe(FamilyRequestStatus::Seen);
});

it('el docente por materia no ve ni atiende las solicitudes', function () {
    [$madre, $nino, , $materia] = requestSetup();
    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation', 'note' => 'Tos']);

    $this->actingAs($materia)->get(route('educator.groups.index'))->assertDontSee('Solicitudes de familias');
    $this->actingAs($materia)->post(route('educator.requests.handle', FamilyRequest::first()))->assertForbidden();
});

it('en primaria el director ve las solicitudes en Grupos', function () {
    $school = School::factory()->type(SchoolType::Primary)->create();
    $grupo = Classroom::factory()->level(GradeLevel::Primero)->create(['school_id' => $school->id]);
    $director = User::factory()->educator()->create(['school_id' => $school->id]);
    $grupo->educators()->attach($director);
    $grupo->update(['director_id' => $director->id]);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id]);
    $madre = User::factory()->guardian()->create(['school_id' => $school->id]);
    $madre->children()->attach($nino);

    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation', 'note' => 'Tos']);

    $this->actingAs($director)->get(route('educator.groups.index'))->assertSee('Solicitudes de familias');
});

it('una familia no envía solicitudes por el hijo de otra', function () {
    [, $nino] = requestSetup();
    $otra = User::factory()->guardian()->create();
    $otra->children()->attach(Child::factory()->create());

    $this->actingAs($otra)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation', 'note' => 'x'])
        ->assertForbidden();
});

it('coordinación ve todas las solicitudes y las filtra', function () {
    [$madre, $nino] = requestSetup();
    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), ['type' => 'recommendation', 'note' => 'Tos']);
    $this->actingAs($madre)->post(route('guardian.requests.store', $nino), [
        'type' => 'excuse', 'date' => today()->toDateString(), 'reason' => 'Enfermedad',
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.requests.index'))
        ->assertOk()->assertSee('Recomendación')->assertSee('Excusa: Enfermedad');

    $this->actingAs($admin)->get(route('admin.requests.index', ['tipo' => 'excuse']))
        ->assertSee('Excusa: Enfermedad')->assertDontSee('Tos');
});

it('la familia tiene Solicitudes en su menú', function () {
    [$madre] = requestSetup();

    expect(collect(\App\Support\Navigation::for($madre))->pluck('label'))->toContain('Solicitudes');
});

it('muestra el formulario de cada tipo de solicitud', function (string $type, string $text) {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->get(route('guardian.requests.create', [$nino, $type]))
        ->assertOk()->assertSee($text);
})->with([
    ['excuse', 'Motivo'],
    ['permission', 'Nombre de quien recoge'],
    ['recommendation', 'Para el cuidado de Ana'],
]);

it('responde 404 con un tipo de solicitud inventado', function () {
    [$madre, $nino] = requestSetup();

    $this->actingAs($madre)->get(route('guardian.requests.create', [$nino, 'inventado']))->assertNotFound();
});
