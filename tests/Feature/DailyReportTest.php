<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\User;

function reportSetup(): array
{
    $classroom = Classroom::factory()->create(['name' => 'Caminadores']);
    $child = Child::create([
        'classroom_id' => $classroom->id,
        'first_name' => 'Ana',
        'last_name' => 'Pérez',
        'birth_date' => '2023-01-01',
    ]);
    $educator = User::factory()->educator()->create();
    $educator->classrooms()->attach($classroom);
    $guardian = User::factory()->guardian()->create();
    $child->guardians()->attach($guardian, ['relationship' => 'Mamá']);

    return [$child, $educator, $guardian];
}

it('permite a la educadora del grupo publicar el reporte', function () {
    [$child, $educator] = reportSetup();

    $this->actingAs($educator)
        ->post(route('educator.reports.store', $child), [
            'mood' => 'happy',
            'meal_status' => 'all',
            'nap_minutes' => 60,
        ])
        ->assertRedirect(route('educator.reports.index'));

    $this->assertDatabaseHas('daily_reports', [
        'child_id' => $child->id,
        'educator_id' => $educator->id,
        'mood' => 'happy',
    ]);
});

it('actualiza el reporte del día en vez de duplicarlo', function () {
    [$child, $educator] = reportSetup();

    $this->actingAs($educator)->post(route('educator.reports.store', $child), ['mood' => 'happy']);
    $this->actingAs($educator)->post(route('educator.reports.store', $child), ['mood' => 'tired']);

    expect(DailyReport::where('child_id', $child->id)->count())->toBe(1);
    expect(DailyReport::first()->mood->value)->toBe('tired');
});

it('impide que una educadora de otro grupo registre al niño', function () {
    [$child] = reportSetup();
    $otra = User::factory()->educator()->create();

    $this->actingAs($otra)
        ->post(route('educator.reports.store', $child), ['mood' => 'happy'])
        ->assertForbidden();

    expect(DailyReport::count())->toBe(0);
});

it('impide que una familia publique reportes', function () {
    [$child, , $guardian] = reportSetup();

    $this->actingAs($guardian)
        ->post(route('educator.reports.store', $child), ['mood' => 'happy'])
        ->assertForbidden();
});

it('rechaza un estado de ánimo inventado', function () {
    [$child, $educator] = reportSetup();

    $this->actingAs($educator)
        ->post(route('educator.reports.store', $child), ['mood' => 'furioso'])
        ->assertSessionHasErrors('mood');
});

it('muestra a la familia solo los reportes publicados de su hijo', function () {
    [$child, $educator, $guardian] = reportSetup();

    DailyReport::create([
        'child_id' => $child->id,
        'report_date' => today()->subDay(),
        'note' => 'Borrador secreto',
    ]);
    $publicado = new DailyReport(['child_id' => $child->id, 'report_date' => today(), 'note' => 'Día feliz']);
    $publicado->educator_id = $educator->id;
    $publicado->published_at = now();
    $publicado->save();

    $this->actingAs($guardian)
        ->get(route('guardian.reports.index', $child))
        ->assertOk()
        ->assertSee('Día feliz')
        ->assertDontSee('Borrador secreto');
});

it('impide que una familia vea la bitácora de un niño ajeno', function () {
    [$child] = reportSetup();
    $intrusa = User::factory()->guardian()->create();

    $this->actingAs($intrusa)
        ->get(route('guardian.reports.index', $child))
        ->assertForbidden();
});