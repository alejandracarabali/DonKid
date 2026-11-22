<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/*
|--------------------------------------------------------------------------
| Datos de inscripción (RF-34)
|--------------------------------------------------------------------------
| Compartidos por EnrollmentApplicationTest y EnrollmentDocumentsTest.
*/

function enrollmentData(array $overrides = []): array
{
    return array_merge([
        'child_first_name' => 'Sofía',
        'child_last_name' => 'Gómez',
        'child_birth_date' => now()->subYears(3)->toDateString(),
        'desired_level' => 'jardin',
        'guardian_name' => 'Marta Gómez',
        'guardian_document_type' => 'cc',
        'guardian_document_number' => '1020304050',
        'guardian_relationship' => 'Madre',
        'guardian_email' => 'marta@correo.co',
        'guardian_phone' => '300 123 4567',
        'guardian_address' => 'Calle 10 # 20-30, Cali',
        'consent_data' => '1',
        'consent_health' => '1',
        'documents' => enrollmentFiles(),
    ], $overrides);
}

function enrollmentFiles(): array
{
    return [
        'birth_certificate' => UploadedFile::fake()->create('registro.pdf', 300, 'application/pdf'),
        'vaccination_card' => UploadedFile::fake()->image('vacunas.jpg'),
        'eps_certificate' => UploadedFile::fake()->create('eps.pdf', 120, 'application/pdf'),
        'photo' => UploadedFile::fake()->image('foto.png', 400, 400),
    ];
}
