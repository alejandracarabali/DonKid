<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\User;

// Docente con un estudiante en su grupo.
function teacherWithChild(): array
{
    $grupo = Classroom::factory()->create();
    $docente = User::factory()->educator()->create();
    $docente->classrooms()->attach($grupo);
    $child = Child::factory()->create(['classroom_id' => $grupo->id]);

    return [$docente, $child];
}

test('un formulario nuevo no trae nada marcado y la siesta sin registrar', function () {
    [$docente, $child] = teacherWithChild();

    $html = $this->actingAs($docente)
        ->get(route('educator.reports.create', $child))
        ->assertOk()
        ->assertSee('Sin registrar')
        ->getContent();

    expect(substr_count($html, 'aria-pressed="true"'))->toBe(0);
});

test('al editar, el ánimo y la comida guardados aparecen marcados', function () {
    [$docente, $child] = teacherWithChild();
    DailyReport::forceCreate([
        'child_id' => $child->id,
        'report_date' => today(),
        'mood' => 'calm',
        'meal_status' => 'all',
        'nap_minutes' => 45,
    ]);

    $html = $this->actingAs($docente)
        ->get(route('educator.reports.create', $child))
        ->assertOk()
        ->assertSee('45 min')
        ->getContent();

    // Uno en el grupo de ánimo y otro en el de comida.
    expect(substr_count($html, 'aria-pressed="true"'))->toBe(2);
});