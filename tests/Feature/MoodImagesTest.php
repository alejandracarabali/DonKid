<?php

use App\Enums\Mood;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\User;

// asset() solo arma la URL: si el archivo no existe, nadie se entera.
// Este test revisa que cada ánimo tenga su imagen en public/images/emociones.
it('tiene una imagen existente para cada estado de ánimo', function () {
    foreach (Mood::cases() as $mood) {
        $path = public_path('images/emociones/'.basename($mood->image()));

        expect(file_exists($path))->toBeTrue("Falta la imagen de {$mood->label()}: {$path}");
    }
});

it('muestra las ilustraciones de DonKid en el formulario de la bitácora', function () {
    $educator = User::factory()->educator()->create();
    $classroom = Classroom::factory()->create();
    $educator->classrooms()->attach($classroom);
    $child = Child::factory()->create(['classroom_id' => $classroom->id]);

    $response = $this->actingAs($educator)
        ->get(route('educator.reports.create', $child))
        ->assertOk();

    foreach (Mood::cases() as $mood) {
        $response->assertSee($mood->image(), false)->assertSee($mood->label());
    }
});

it('muestra la ilustración del ánimo en la bitácora de la familia', function () {
    $guardian = User::factory()->guardian()->create();
    $child = Child::factory()->create();
    $guardian->children()->attach($child);

    $report = DailyReport::create(['child_id' => $child->id, 'report_date' => today(), 'mood' => Mood::Angry]);
    $report->forceFill(['published_at' => now()])->save(); // solo lo publicado lo ve la familia

    $this->actingAs($guardian)
        ->get(route('guardian.reports.index', $child))
        ->assertOk()
        ->assertSee(Mood::Angry->image(), false)
        ->assertSee('Enojado');
});