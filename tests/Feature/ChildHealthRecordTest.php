<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// RF-38: ficha de salud del niño.

function healthData(array $overrides = []): array
{
    return array_merge([
        'eps' => 'Sura',
        'conditions' => 'Asma leve',
        'medications' => 'Salbutamol, 2 inhalaciones si hay tos',
        'emergency_contact_name' => 'Luis Gómez',
        'emergency_contact_phone' => '300 111 2233',
    ], $overrides);
}

it('coordinación guarda la ficha de salud al crear un niño', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.children.store'), [
        'first_name' => 'Ana',
        'last_name' => 'Gómez',
        'birth_date' => '2023-03-01',
        'health' => healthData(),
    ])->assertSessionHasNoErrors();

    $ficha = Child::where('first_name', 'Ana')->first()->healthRecord;

    expect($ficha->eps)->toBe('Sura')
        ->and($ficha->conditions)->toBe('Asma leve')
        ->and($ficha->emergency_contact_phone)->toBe('300 111 2233');
});

it('actualiza la ficha existente en vez de crear otra', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create();
    $nino->healthRecord()->create(healthData());

    $this->actingAs($admin)->patch(route('admin.children.update', $nino), [
        'first_name' => $nino->first_name,
        'last_name' => $nino->last_name,
        'birth_date' => $nino->birth_date->toDateString(),
        'health' => healthData(['eps' => 'Sanitas']),
    ])->assertSessionHasNoErrors();

    expect(DB::table('child_health_records')->where('child_id', $nino->id)->count())->toBe(1)
        ->and($nino->fresh()->healthRecord->eps)->toBe('Sanitas');
});

it('guarda condiciones y medicamentos cifrados en la base de datos', function () {
    $nino = Child::factory()->create();
    $nino->healthRecord()->create(healthData());

    $crudo = DB::table('child_health_records')->where('child_id', $nino->id)->first();

    expect($crudo->conditions)->not->toContain('Asma')
        ->and($crudo->medications)->not->toContain('Salbutamol')
        ->and($nino->fresh()->healthRecord->conditions)->toBe('Asma leve'); // al leer, se descifra
});

it('rechaza un teléfono de emergencia inválido', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.children.store'), [
        'first_name' => 'Ana',
        'last_name' => 'Gómez',
        'birth_date' => '2023-03-01',
        'health' => healthData(['emergency_contact_phone' => 'llamar a Luis']),
    ])->assertSessionHasErrors('health.emergency_contact_phone');
});

it('la docente del grupo ve la ficha al llenar la bitácora', function () {
    $grupo = Classroom::factory()->create();
    $docente = User::factory()->educator()->create();
    $docente->classrooms()->attach($grupo);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id, 'allergies' => 'Maní']);
    $nino->healthRecord()->create(healthData());

    $this->actingAs($docente)->get(route('educator.reports.create', $nino))
        ->assertOk()
        ->assertSee('Maní')
        ->assertSee('Asma leve')
        ->assertSee('Salbutamol')
        ->assertSee('tel:300 111 2233', false);
});

it('una docente de otro grupo no ve la ficha', function () {
    $otro = User::factory()->educator()->create();
    $otro->classrooms()->attach(Classroom::factory()->create());
    $nino = Child::factory()->create();
    $nino->healthRecord()->create(healthData());

    $this->actingAs($otro)->get(route('educator.reports.create', $nino))->assertForbidden();
});

it('muestra la ficha de salud en los formularios de coordinación', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create();
    $nino->healthRecord()->create(healthData());

    $this->actingAs($admin)->get(route('admin.children.create'))
        ->assertOk()->assertSee('Ficha de salud');

    $this->actingAs($admin)->get(route('admin.children.edit', $nino))
        ->assertOk()->assertSee('Ficha de salud')->assertSee('Asma leve');
});
