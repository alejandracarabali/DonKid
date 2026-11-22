<?php

use App\Enums\EnrollmentDocumentType;
use App\Models\EnrollmentApplication;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// RF-34: los 4 documentos obligatorios de la inscripción.

beforeEach(fn () => Storage::fake('local'));

function docsSchool(): School
{
    return School::query()->orderBy('id')->first();
}

it('guarda los 4 documentos en el disco privado', function () {
    $this->post(route('enrollment.store', docsSchool()), enrollmentData())->assertSessionHasNoErrors();

    $solicitud = EnrollmentApplication::first();

    expect($solicitud->documents)->toHaveCount(4);

    foreach ($solicitud->documents as $doc) {
        Storage::disk('local')->assertExists($doc->path);
        expect($doc->path)->toStartWith("inscripciones/{$solicitud->id}/");
    }
});

it('no usa el nombre original del archivo como ruta', function () {
    $files = enrollmentFiles();
    $files['birth_certificate'] = UploadedFile::fake()->create('../../hack.pdf', 10, 'application/pdf');

    $this->post(route('enrollment.store', docsSchool()), enrollmentData(['documents' => $files]));

    $doc = EnrollmentApplication::first()->documents->firstWhere('type', EnrollmentDocumentType::BirthCertificate);

    expect($doc->path)->not->toContain('hack')
        ->and($doc->path)->not->toContain('..');
});

it('exige los 4 documentos', function () {
    $this->post(route('enrollment.store', docsSchool()), enrollmentData(['documents' => []]))
        ->assertSessionHasErrors([
            'documents.birth_certificate', 'documents.vaccination_card',
            'documents.eps_certificate', 'documents.photo',
        ]);

    expect(EnrollmentApplication::count())->toBe(0);
});

it('solo acepta imágenes como foto del niño', function () {
    $files = enrollmentFiles();
    $files['photo'] = UploadedFile::fake()->create('foto.pdf', 50, 'application/pdf');

    $this->post(route('enrollment.store', docsSchool()), enrollmentData(['documents' => $files]))
        ->assertSessionHasErrors('documents.photo');
});

it('rechaza formatos no permitidos', function () {
    $files = enrollmentFiles();
    $files['eps_certificate'] = UploadedFile::fake()->create('virus.exe', 50, 'application/x-msdownload');

    $this->post(route('enrollment.store', docsSchool()), enrollmentData(['documents' => $files]))
        ->assertSessionHasErrors('documents.eps_certificate');

    expect(EnrollmentApplication::count())->toBe(0);
});

it('rechaza archivos de más de 4 MB', function () {
    $files = enrollmentFiles();
    $files['birth_certificate'] = UploadedFile::fake()->create('grande.pdf', 5000, 'application/pdf');

    $this->post(route('enrollment.store', docsSchool()), enrollmentData(['documents' => $files]))
        ->assertSessionHasErrors('documents.birth_certificate');
});

it('muestra los campos de documentos en el formulario', function () {
    $response = $this->get(route('enrollment.create', docsSchool()))
        ->assertOk()
        ->assertSee('enctype="multipart/form-data"', false);

    foreach (EnrollmentDocumentType::cases() as $type) {
        $response->assertSee($type->label());
    }
});
