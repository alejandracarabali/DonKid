<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\User;

// Docente con un grupo y estudiantes. $enviados: nombres que ya tienen bitácora hoy.
function groupWithReports(array $nombres, array $enviados): User
{
    $grupo = Classroom::factory()->create();
    // Nombres fijos: así ningún nombre aleatorio contiene "Ana" y confunde al test.
    $docente = User::factory()->educator()->create(['name' => 'Profe Sol']);
    $docente->classrooms()->attach($grupo);

    foreach ($nombres as $nombre) {
        $child = Child::factory()->create([
            'classroom_id' => $grupo->id,
            'first_name' => $nombre,
            'last_name' => 'Prueba',
        ]);

        if (in_array($nombre, $enviados, true)) {
            DailyReport::forceCreate(['child_id' => $child->id, 'report_date' => today()]);
        }
    }

    return $docente;
}

test('muestra el avance del día', function () {
    $docente = groupWithReports(['Ana', 'Beto', 'Caro'], ['Ana']);

    $this->actingAs($docente)
        ->get(route('educator.reports.index'))
        ->assertOk()
        ->assertSee('1 de 3 enviados')
        ->assertSee('aria-valuenow="1"', false);
});

test('muestra primero a quienes están pendientes', function () {
    $docente = groupWithReports(['Ana', 'Beto'], ['Ana']);

    $this->actingAs($docente)
        ->get(route('educator.reports.index'))
        ->assertSeeInOrder(['Beto', '○ Pendiente', 'Ana', '✓ Enviado']);
});

test('celebra cuando todo el grupo está al día', function () {
    $docente = groupWithReports(['Ana', 'Beto'], ['Ana', 'Beto']);

    $this->actingAs($docente)
        ->get(route('educator.reports.index'))
        ->assertSee('¡Todo el grupo al día!');
});