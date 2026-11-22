<?php

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\EnrollmentApplication;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

// RF-35: bandeja de inscripciones de coordinación.

beforeEach(fn () => Storage::fake('local'));

// Envía una solicitud por el formulario público y la devuelve.
function submitApplication(array $overrides = []): EnrollmentApplication
{
    $school = School::query()->orderBy('id')->first();
    test()->post(route('enrollment.store', $school), enrollmentData($overrides))->assertSessionHasNoErrors();

    return EnrollmentApplication::withoutGlobalScopes()->latest('id')->first();
}

it('solo coordinación entra a la bandeja', function () {
    $this->actingAs(User::factory()->educator()->create())->get(route('admin.enrollments.index'))->assertForbidden();
    $this->actingAs(User::factory()->guardian()->create())->get(route('admin.enrollments.index'))->assertForbidden();
});

it('lista las solicitudes pendientes y las cuenta en el menú', function () {
    submitApplication();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('admin.enrollments.index'))
        ->assertOk()
        ->assertSee('Sofía Gómez')
        ->assertSee('INS-00001')
        ->assertSee('Pendiente (1)');

    expect(collect(\App\Support\Navigation::for($admin))->firstWhere('label', 'Inscripciones')['badge'])->toBe(1);
});

it('muestra el detalle con el documento descifrado', function () {
    $solicitud = submitApplication();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.enrollments.show', $solicitud))
        ->assertOk()
        ->assertSee('1020304050')
        ->assertSee('Registro civil');
});

it('abre los documentos solo para coordinación de ese colegio', function () {
    $solicitud = submitApplication();
    $doc = $solicitud->documents()->first();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.enrollments.document', [$solicitud, $doc]))
        ->assertOk()
        ->assertHeader('Cache-Control', 'no-store, private');

    $otroColegio = School::factory()->create();
    $this->actingAs(User::factory()->admin()->create(['school_id' => $otroColegio->id]))
        ->get(route('admin.enrollments.document', [$solicitud, $doc]))
        ->assertNotFound();

    $this->actingAs(User::factory()->educator()->create())
        ->get(route('admin.enrollments.document', [$solicitud, $doc]))
        ->assertForbidden();
});

it('no abre un documento de otra solicitud por la URL', function () {
    $una = submitApplication();
    $otra = submitApplication(['child_first_name' => 'Mateo']);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.enrollments.document', [$una, $otra->documents()->first()]))
        ->assertNotFound();
});

it('al aprobar crea el niño, la cuenta del acudiente y los vincula', function () {
    $solicitud = submitApplication();
    $grupo = Classroom::factory()->create(['name' => 'Girasoles']);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => $grupo->id])
        ->assertRedirect(route('admin.enrollments.index'))
        ->assertSessionHas('temporal_password');

    $solicitud->refresh();
    $nino = Child::find($solicitud->child_id);
    $familia = User::withoutGlobalScopes()->where('email', 'marta@correo.co')->first();

    expect($solicitud->status)->toBe(EnrollmentStatus::Approved)
        ->and($solicitud->reviewed_at)->not->toBeNull()
        ->and($nino->classroom_id)->toBe($grupo->id)
        ->and($nino->first_name)->toBe('Sofía')
        ->and($familia->role)->toBe(Role::Guardian)
        ->and($familia->must_change_password)->toBeTrue()
        ->and($familia->school_id)->toBe($solicitud->school_id)
        ->and($nino->guardians()->first()->pivot->relationship)->toBe('Madre');
});

it('vincula a un hermano con la cuenta que la familia ya tiene', function () {
    $madre = User::factory()->guardian()->create(['email' => 'marta@correo.co']);
    $solicitud = submitApplication();
    $usuariosAntes = User::withoutGlobalScopes()->count();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => Classroom::factory()->create()->id])
        ->assertSessionMissing('temporal_password');

    expect(User::withoutGlobalScopes()->count())->toBe($usuariosAntes + 1) // solo el admin del test
        ->and($madre->children()->pluck('first_name')->all())->toBe(['Sofía']);
});

it('no aprueba si el correo es de otra cuenta, y no deja nada a medias', function () {
    User::factory()->educator()->create(['email' => 'marta@correo.co']);
    $solicitud = submitApplication();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => Classroom::factory()->create()->id])
        ->assertSessionHasErrors('classroom_id');

    expect($solicitud->fresh()->status)->toBe(EnrollmentStatus::Pending)
        ->and(Child::where('first_name', 'Sofía')->exists())->toBeFalse();
});

it('no acepta un grupo de otro colegio', function () {
    $solicitud = submitApplication();
    $ajeno = Classroom::factory()->create(['school_id' => School::factory()->create()->id]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => $ajeno->id])
        ->assertSessionHasErrors('classroom_id');
});

it('rechaza con motivo obligatorio', function () {
    $solicitud = submitApplication();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->post(route('admin.enrollments.reject', $solicitud), [])
        ->assertSessionHasErrors('rejection_reason');

    $this->actingAs($admin)->post(route('admin.enrollments.reject', $solicitud), ['rejection_reason' => 'No hay cupo'])
        ->assertRedirect(route('admin.enrollments.index'));

    expect($solicitud->fresh()->status)->toBe(EnrollmentStatus::Rejected)
        ->and($solicitud->fresh()->rejection_reason)->toBe('No hay cupo');
});

it('no deja revisar dos veces la misma solicitud', function () {
    $solicitud = submitApplication();
    $admin = User::factory()->admin()->create();
    $grupo = Classroom::factory()->create();

    $this->actingAs($admin)->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => $grupo->id]);
    $this->actingAs($admin)->post(route('admin.enrollments.approve', $solicitud), ['classroom_id' => $grupo->id])->assertStatus(409);
    $this->actingAs($admin)->post(route('admin.enrollments.reject', $solicitud), ['rejection_reason' => 'x'])->assertStatus(409);

    expect(Child::where('first_name', 'Sofía')->count())->toBe(1);
});
