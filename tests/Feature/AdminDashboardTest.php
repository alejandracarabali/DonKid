<?php

use App\Enums\MealStatus;
use App\Enums\Mood;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\School;
use App\Models\User;

// Bitácora de hoy para un estudiante. DailyReport no tiene fábrica,
// así que se crea a mano; educator_id lo asigna el servidor, no el formulario.
function reportToday(Child $child, User $educator): DailyReport
{
    $report = new DailyReport([
        'child_id' => $child->id,
        'report_date' => today(),
        'mood' => Mood::cases()[0],
        'meal_status' => MealStatus::cases()[0],
        'nap_minutes' => 0,
    ]);
    $report->educator_id = $educator->id;
    $report->save();

    return $report;
}

test('coordinación ve las cifras de su colegio', function () {
    $admin = User::factory()->admin()->create();
    $grupo = Classroom::factory()->create();
    Child::factory()->count(3)->create(['classroom_id' => $grupo->id]);
    User::factory()->educator()->create();
    User::factory()->guardian()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('stats', fn (array $stats) => collect($stats)->pluck('value', 'label')->all() === [
            'Grupos' => 1,
            'Estudiantes' => 3,
            'Familias' => 1,
            'Docentes' => 1,
        ]);
});

test('el avance de bitácoras usa la misma regla que Mi grupo', function () {
    $admin = User::factory()->admin()->create();
    $docente = User::factory()->educator()->create();
    $grupo = Classroom::factory()->create(['name' => 'Caminadores']);
    [$conBitacora] = Child::factory()->count(2)->create(['classroom_id' => $grupo->id]);
    reportToday($conBitacora, $docente);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewHas('sent', 1)
        ->assertViewHas('total', 2)
        ->assertSee('Caminadores');
});

test('las cifras no incluyen datos de otro colegio', function () {
    $admin = User::factory()->admin()->create();
    $otro = School::factory()->create();
    $grupoAjeno = Classroom::factory()->create(['school_id' => $otro->id, 'name' => 'Grupo Ajeno']);
    Child::factory()->create(['classroom_id' => $grupoAjeno->id]);
    User::factory()->educator()->create(['school_id' => $otro->id]);

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Grupo Ajeno')
        ->assertViewHas('stats', fn (array $stats) => collect($stats)->every(fn ($s) => $s['value'] === 0));
});

test('docentes y familias no entran al inicio de coordinación', function (string $state) {
    $this->actingAs(User::factory()->{$state}()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
})->with(['educator', 'guardian']);