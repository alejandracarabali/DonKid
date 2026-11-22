<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;

// RF-61 y RF-62: director de grupo.

// Grupo de preescolar con director y un docente por materia.
function groupWithDirector(): array
{
    $grupo = Classroom::factory()->create(['name' => 'Girasoles']);
    $director = User::factory()->educator()->create(['name' => 'Sol Parra']);
    $materia = User::factory()->educator()->create(['name' => 'Pedro Música']);
    $grupo->educators()->attach([$director->id, $materia->id]);
    $grupo->update(['director_id' => $director->id]);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ana']);

    return [$grupo, $director, $materia, $nino];
}

it('coordinación asigna el director y queda también como docente del grupo', function () {
    $admin = User::factory()->admin()->create();
    $docente = User::factory()->educator()->create();

    $this->actingAs($admin)->post(route('admin.classrooms.store'), [
        'name' => 'Girasoles', 'level' => 'jardin', 'director_id' => $docente->id,
    ])->assertSessionHasNoErrors();

    $grupo = Classroom::where('name', 'Girasoles')->first();

    expect($grupo->director_id)->toBe($docente->id)
        ->and($grupo->educators()->whereKey($docente->id)->exists())->toBeTrue();
});

it('un grupo tiene un solo director: asignar otro reemplaza al anterior', function () {
    [$grupo, , $materia] = groupWithDirector();

    $this->actingAs(User::factory()->admin()->create())->patch(route('admin.classrooms.update', $grupo), [
        'name' => $grupo->name, 'level' => 'jardin', 'director_id' => $materia->id,
    ])->assertSessionHasNoErrors();

    expect($grupo->fresh()->director_id)->toBe($materia->id);
});

it('el director debe ser un docente del mismo colegio', function () {
    $admin = User::factory()->admin()->create();
    $familia = User::factory()->guardian()->create();
    $ajeno = User::factory()->educator()->create(['school_id' => School::factory()->create()->id]);

    foreach ([$familia, $ajeno] as $candidato) {
        $this->actingAs($admin)->post(route('admin.classrooms.store'), [
            'name' => 'Grupo '.$candidato->id, 'level' => 'jardin', 'director_id' => $candidato->id,
        ])->assertSessionHasErrors('director_id');
    }
});

it('solo el director registra la bitácora; el docente por materia no', function () {
    [, $director, $materia, $nino] = groupWithDirector();

    $this->actingAs($director)->get(route('educator.reports.create', $nino))->assertOk();
    $this->actingAs($materia)->get(route('educator.reports.create', $nino))->assertForbidden();
    $this->actingAs($materia)->post(route('educator.reports.store', $nino), ['mood' => 'happy'])->assertForbidden();
});

it('el docente solo por materia no ve la bitácora en su menú y empieza en Grupos', function () {
    [, , $materia] = groupWithDirector();

    $this->actingAs($materia)->get(route('educator.dashboard'))->assertRedirect(route('educator.groups.index'));

    $nav = collect(\App\Support\Navigation::for($materia))->pluck('label');
    expect($nav)->not->toContain('Bitácora')->and($nav)->toContain('Grupos');
});

it('el director ve la bitácora de su grupo', function () {
    [, $director] = groupWithDirector();

    $this->actingAs($director)->get(route('educator.dashboard'))->assertRedirect(route('educator.reports.index'));
    $this->actingAs($director)->get(route('educator.reports.index'))->assertOk()->assertSee('Ana');
});

it('un grupo sin director sigue funcionando con todos sus docentes (transición)', function () {
    $grupo = Classroom::factory()->create(['director_id' => null]);
    $docente = User::factory()->educator()->create();
    $grupo->educators()->attach($docente);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id]);

    $this->actingAs($docente)->get(route('educator.reports.create', $nino))->assertOk();
});

it('la familia ve quién es el director de grupo de su hijo', function () {
    [, , , $nino] = groupWithDirector();
    $madre = User::factory()->guardian()->create();
    $madre->children()->attach($nino);

    $this->actingAs($madre)->get(route('guardian.dashboard'))
        ->assertOk()
        ->assertSee('Director de grupo: Sol Parra');
});

it('coordinación ve el director en la lista de grupos', function () {
    groupWithDirector();

    $this->actingAs(User::factory()->admin()->create())->get(route('admin.classrooms.index'))
        ->assertSee('Director: Sol Parra');
});
