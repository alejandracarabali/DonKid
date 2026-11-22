<?php

use App\Models\EnrollmentApplication;
use App\Models\School;
use Illuminate\Support\Facades\Storage;

// RF-37: autorización de tratamiento de datos en la inscripción.

beforeEach(fn () => Storage::fake('local'));

function consentSchool(): School
{
    return School::query()->orderBy('id')->first();
}

it('exige la autorización general para enviar la solicitud', function () {
    $this->post(route('enrollment.store', consentSchool()), enrollmentData(['consent_data' => null]))
        ->assertSessionHasErrors('consent_data');

    expect(EnrollmentApplication::count())->toBe(0);
});

it('guarda la prueba de la autorización: fecha, IP y versión del texto', function () {
    config(['privacy.policy_version' => '2026-11-v1']);

    $this->withServerVariables(['REMOTE_ADDR' => '190.25.10.4'])
        ->post(route('enrollment.store', consentSchool()), enrollmentData())
        ->assertSessionHasNoErrors();

    $solicitud = EnrollmentApplication::first();

    expect($solicitud->data_consent_at)->not->toBeNull()
        ->and($solicitud->health_consent_at)->not->toBeNull()
        ->and($solicitud->consent_ip)->toBe('190.25.10.4')
        ->and($solicitud->consent_policy_version)->toBe('2026-11-v1');
});

it('no deja que el formulario falsifique la prueba de autorización', function () {
    $this->post(route('enrollment.store', consentSchool()), enrollmentData([
        'consent_ip' => '1.1.1.1',
        'consent_policy_version' => 'inventada',
    ]));

    $solicitud = EnrollmentApplication::first();

    expect($solicitud->consent_ip)->not->toBe('1.1.1.1')
        ->and($solicitud->consent_policy_version)->toBe(config('privacy.policy_version'));
});

describe('documentos de salud obligatorios (configuración actual)', function () {
    beforeEach(fn () => config(['privacy.health_documents_required' => true]));

    it('exige la autorización de datos de salud', function () {
        $this->post(route('enrollment.store', consentSchool()), enrollmentData(['consent_health' => null]))
            ->assertSessionHasErrors('consent_health');
    });
});

describe('documentos de salud opcionales (si el abogado lo indica)', function () {
    beforeEach(fn () => config(['privacy.health_documents_required' => false]));

    it('permite inscribir sin documentos de salud ni autorización de salud', function () {
        $files = enrollmentFiles();
        unset($files['vaccination_card'], $files['eps_certificate']);

        $this->post(route('enrollment.store', consentSchool()), enrollmentData([
            'consent_health' => null,
            'documents' => $files,
        ]))->assertSessionHasNoErrors();

        $solicitud = EnrollmentApplication::first();

        expect($solicitud->documents)->toHaveCount(2)
            ->and($solicitud->health_consent_at)->toBeNull();
    });

    it('rechaza documentos de salud si no hay autorización de salud', function () {
        $this->post(route('enrollment.store', consentSchool()), enrollmentData(['consent_health' => null]))
            ->assertSessionHasErrors(['documents.vaccination_card', 'documents.eps_certificate']);

        expect(EnrollmentApplication::count())->toBe(0);
    });

    it('marca los documentos de salud como opcionales en el formulario', function () {
        $this->get(route('enrollment.create', consentSchool()))
            ->assertSee('Carné de vacunas (opcional)')
            ->assertSee('Certificado de la EPS (opcional)');
    });
});

it('muestra la política de datos enlazada desde el formulario', function () {
    $school = consentSchool();

    $this->get(route('enrollment.create', $school))
        ->assertSee(route('enrollment.policy', $school), false);

    $this->get(route('enrollment.policy', $school))
        ->assertOk()
        ->assertSee('Política de tratamiento de datos')
        ->assertSee(config('privacy.policy_version'));
});
