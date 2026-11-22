<?php

use App\Enums\EnrollmentStatus;
use App\Enums\SchoolType;
use App\Models\EnrollmentApplication;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('local'));

// RF-34: formulario público de inscripción.

function demoSchool(): School
{
    return School::query()->orderBy('id')->first(); // "Colegio Demo" (jardín y primaria)
}

it('muestra el formulario sin iniciar sesión, con la URL del colegio', function () {
    $school = demoSchool();

    $this->get(route('enrollment.create', $school))
        ->assertOk()
        ->assertSee('Inscripción')
        ->assertSee($school->name);

    expect(route('enrollment.create', $school))->toEndWith('/inscripcion/'.$school->slug);
});

it('guarda la solicitud como pendiente en el colegio del enlace', function () {
    $school = demoSchool();

    $this->post(route('enrollment.store', $school), enrollmentData())
        ->assertRedirect(route('enrollment.sent', $school))
        ->assertSessionHas('reference');

    $solicitud = EnrollmentApplication::first();

    expect($solicitud->school_id)->toBe($school->id)
        ->and($solicitud->status)->toBe(EnrollmentStatus::Pending)
        ->and($solicitud->child_first_name)->toBe('Sofía')
        ->and($solicitud->guardian_document_number)->toBe('1020304050');
});

it('muestra el número de solicitud a la familia', function () {
    $school = demoSchool();

    $this->followingRedirects()
        ->post(route('enrollment.store', $school), enrollmentData())
        ->assertOk()
        ->assertSee('recibimos tu solicitud')
        ->assertSee('INS-00001');
});

it('cifra el documento y la dirección del acudiente', function () {
    $this->post(route('enrollment.store', demoSchool()), enrollmentData());

    $crudo = DB::table('enrollment_applications')->first();

    expect($crudo->guardian_document_number)->not->toContain('1020304050')
        ->and($crudo->guardian_address)->not->toContain('Calle 10');
});

it('exige los datos obligatorios', function () {
    $this->post(route('enrollment.store', demoSchool()), [])
        ->assertSessionHasErrors([
            'child_first_name', 'child_last_name', 'child_birth_date', 'desired_level',
            'guardian_name', 'guardian_document_type', 'guardian_document_number',
            'guardian_relationship', 'guardian_email', 'guardian_phone', 'guardian_address',
        ]);

    expect(EnrollmentApplication::count())->toBe(0);
});

it('solo acepta niveles que ofrece el colegio', function () {
    $primaria = School::factory()->type(SchoolType::Primary)->create();

    $this->post(route('enrollment.store', $primaria), enrollmentData(['desired_level' => 'jardin']))
        ->assertSessionHasErrors('desired_level');

    $this->post(route('enrollment.store', $primaria), enrollmentData(['desired_level' => 'primero']))
        ->assertSessionHasNoErrors();
});

it('rechaza envíos de bots que llenan el campo trampa', function () {
    $this->post(route('enrollment.store', demoSchool()), enrollmentData(['website' => 'http://spam.example']))
        ->assertSessionHasErrors('website');

    expect(EnrollmentApplication::count())->toBe(0);
});

it('no permite que el formulario cambie el estado o el colegio', function () {
    $school = demoSchool();
    $otro = School::factory()->create();

    $this->post(route('enrollment.store', $school), enrollmentData([
        'status' => 'approved',
        'school_id' => $otro->id,
    ]));

    $solicitud = EnrollmentApplication::withoutGlobalScopes()->first();

    expect($solicitud->status)->toBe(EnrollmentStatus::Pending)
        ->and($solicitud->school_id)->toBe($school->id);
});

it('limita a 5 envíos por hora desde la misma conexión', function () {
    $school = demoSchool();

    foreach (range(1, 5) as $i) {
        $this->post(route('enrollment.store', $school), enrollmentData())->assertRedirect();
    }

    $this->post(route('enrollment.store', $school), enrollmentData())->assertStatus(429);
});

it('responde 404 para un colegio que no existe', function () {
    $this->get('/inscripcion/colegio-inventado')->assertNotFound();
});

it('coordinación ve el enlace de inscripción en su inicio', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Enlace de inscripción')
        ->assertSee(route('enrollment.create', $admin->school));
});
