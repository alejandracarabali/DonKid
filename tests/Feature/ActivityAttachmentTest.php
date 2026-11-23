<?php

use App\Models\Activity;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// Planeación semanal en PDF: la docente la sube y las familias del grupo la descargan.

beforeEach(function () {
    Storage::fake('local'); // disco de mentira: las pruebas no ensucian storage/
    $this->classroom = Classroom::factory()->create();
    $this->educator = User::factory()->educator()->create();
    $this->educator->classrooms()->attach($this->classroom);
});

// Datos válidos de una actividad, con lo que se quiera cambiar.
function activityData(array $overrides = []): array
{
    return array_merge([
        'classroom_id' => test()->classroom->id,
        'title' => 'Semana de los animales',
        'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
        'ends_at' => now()->addDay()->addHour()->format('Y-m-d\TH:i'),
    ], $overrides);
}

function pdf(string $name = 'Semana 12.pdf'): UploadedFile
{
    return UploadedFile::fake()->create($name, 200, 'application/pdf');
}

it('guarda el PDF en el disco privado con nombre aleatorio', function () {
    $this->actingAs($this->educator)
        ->post(route('educator.activities.store'), activityData(['attachment' => pdf()]))
        ->assertSessionHasNoErrors();

    $activity = Activity::sole();
    expect($activity->attachment_name)->toBe('Semana 12.pdf')
        ->and($activity->attachment_path)->toStartWith('actividades/')
        ->and($activity->attachment_path)->not->toContain('Semana');
    Storage::disk('local')->assertExists($activity->attachment_path);
});

it('la actividad sin PDF se sigue creando normal', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData())->assertSessionHasNoErrors();

    expect(Activity::sole()->attachment_path)->toBeNull();
});

it('rechaza archivos que no son PDF o pesan más de 10 MB', function (UploadedFile $file, string $message) {
    $this->actingAs($this->educator)
        ->post(route('educator.activities.store'), activityData(['attachment' => $file]))
        ->assertSessionHasErrors(['attachment' => $message]);

    expect(Activity::count())->toBe(0);
})->with([
    'imagen' => [fn () => UploadedFile::fake()->image('foto.jpg'), 'El archivo debe ser un PDF.'],
    'muy pesado' => [fn () => UploadedFile::fake()->create('grande.pdf', 11000, 'application/pdf'), 'El PDF pesa más de 10 MB. Prueba con uno más liviano.'],
]);

it('al subir otro PDF borra el anterior', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf('vieja.pdf')]));
    $activity = Activity::sole();
    $old = $activity->attachment_path;

    $this->put(route('educator.activities.update', $activity), activityData(['attachment' => pdf('nueva.pdf')]))
        ->assertSessionHasNoErrors();

    $activity->refresh();
    expect($activity->attachment_name)->toBe('nueva.pdf');
    Storage::disk('local')->assertMissing($old);
    Storage::disk('local')->assertExists($activity->attachment_path);
});

it('editar sin tocar el PDF lo conserva', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf()]));
    $activity = Activity::sole();

    $this->put(route('educator.activities.update', $activity), activityData(['title' => 'Otro título']));

    expect($activity->fresh()->attachment_path)->toBe($activity->attachment_path);
    Storage::disk('local')->assertExists($activity->attachment_path);
});

it('se puede quitar el PDF', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf()]));
    $activity = Activity::sole();
    $path = $activity->attachment_path;

    $this->put(route('educator.activities.update', $activity), activityData(['remove_attachment' => '1']));

    expect($activity->fresh()->attachment_path)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

it('al eliminar la actividad se borra su PDF', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf()]));
    $activity = Activity::sole();

    $this->delete(route('educator.activities.destroy', $activity));

    Storage::disk('local')->assertMissing($activity->attachment_path);
});

it('la familia del grupo ve el enlace y descarga el PDF con su nombre original', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf()]));
    $activity = Activity::sole();
    $guardian = User::factory()->guardian()->create();
    $guardian->children()->attach(Child::factory()->create(['classroom_id' => $this->classroom->id]));

    $this->actingAs($guardian)
        ->get(route('guardian.activities.index', ['semana' => now()->addDay()->toDateString()]))
        ->assertSee(route('activities.attachment', $activity));

    $this->get(route('activities.attachment', $activity))
        ->assertOk()
        ->assertDownload('Semana 12.pdf');
});

it('una familia de otro grupo no puede descargarlo', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData(['attachment' => pdf()]));
    $guardian = User::factory()->guardian()->create();
    $guardian->children()->attach(Child::factory()->create()); // su hijo está en otro grupo

    $this->actingAs($guardian)->get(route('activities.attachment', Activity::sole()))->assertForbidden();
});

it('responde 404 si la actividad no tiene PDF', function () {
    $this->actingAs($this->educator)->post(route('educator.activities.store'), activityData());

    $this->get(route('activities.attachment', Activity::sole()))->assertNotFound();
});