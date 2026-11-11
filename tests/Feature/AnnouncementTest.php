<?php

use App\Models\Announcement;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;

function announcementSetup(): array
{
    $grupo = Classroom::factory()->create(['name' => 'Caminadores']);
    $otro = Classroom::factory()->create(['name' => 'Exploradores']);
    $educator = User::factory()->educator()->create();
    $educator->classrooms()->attach($grupo);
    $guardian = User::factory()->guardian()->create();
    $child = Child::create([
        'classroom_id' => $grupo->id,
        'first_name' => 'Ana',
        'last_name' => 'Pérez',
        'birth_date' => '2023-01-01',
    ]);
    $child->guardians()->attach($guardian, ['relationship' => 'Mamá']);

    return [$grupo, $otro, $educator, $guardian];
}

function publicar(Classroom $grupo, User $autor, string $titulo): Announcement
{
    $a = new Announcement(['title' => $titulo, 'body' => 'Texto del aviso']);
    $a->classroom_id = $grupo->id;
    $a->author_id = $autor->id;
    $a->published_at = now();
    $a->save();

    return $a;
}

it('permite a la educadora publicar en su grupo', function () {
    [$grupo, , $educator] = announcementSetup();

    $this->actingAs($educator)
        ->post(route('educator.announcements.store'), [
            'classroom_id' => $grupo->id,
            'title' => 'Paseo',
            'body' => 'Mañana salimos al parque.',
        ])
        ->assertRedirect(route('educator.dashboard'));

    $this->assertDatabaseHas('announcements', ['title' => 'Paseo', 'author_id' => $educator->id]);
});

it('impide que una educadora publique en otro grupo', function () {
    [, $otro, $educator] = announcementSetup();

    $this->actingAs($educator)
        ->post(route('educator.announcements.store'), [
            'classroom_id' => $otro->id,
            'title' => 'Intruso',
            'body' => 'No debería poder.',
        ])
        ->assertForbidden();

    expect(Announcement::count())->toBe(0);
});

it('impide que una educadora publique un aviso general', function () {
    [, , $educator] = announcementSetup();

    $this->actingAs($educator)
        ->post(route('educator.announcements.store'), [
            'title' => 'General',
            'body' => 'Solo coordinación.',
        ])
        ->assertForbidden();
});

it('permite a coordinación publicar un aviso general', function () {
    announcementSetup();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('educator.announcements.store'), [
            'title' => 'Festivo',
            'body' => 'El lunes no hay jardín.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('announcements', ['title' => 'Festivo', 'classroom_id' => null]);
});

it('impide que una familia publique avisos', function () {
    [, , , $guardian] = announcementSetup();

    $this->actingAs($guardian)
        ->post(route('educator.announcements.store'), ['title' => 'X', 'body' => 'Y'])
        ->assertForbidden();
});

it('muestra a la familia solo los avisos de los grupos de sus hijos', function () {
    [$grupo, $otro, $educator, $guardian] = announcementSetup();
    publicar($grupo, $educator, 'Aviso visible');
    publicar($otro, $educator, 'Aviso ajeno');

    $this->actingAs($guardian)
        ->get(route('guardian.announcements.index'))
        ->assertOk()
        ->assertSee('Aviso visible')
        ->assertDontSee('Aviso ajeno');
});

it('registra la lectura una sola vez', function () {
    [$grupo, , $educator, $guardian] = announcementSetup();
    $aviso = publicar($grupo, $educator, 'Leer');

    $this->actingAs($guardian)->post(route('guardian.announcements.read', $aviso));
    $this->actingAs($guardian)->post(route('guardian.announcements.read', $aviso));

    expect($aviso->readers()->count())->toBe(1);
});

it('impide marcar como leído un aviso de otro grupo', function () {
    [, $otro, $educator, $guardian] = announcementSetup();
    $ajeno = publicar($otro, $educator, 'Ajeno');

    $this->actingAs($guardian)
        ->post(route('guardian.announcements.read', $ajeno))
        ->assertForbidden();
});

it('avisa a una educadora sin grupos que aún no tiene uno asignado', function () {
    $educator = User::factory()->educator()->create();

    $this->actingAs($educator)
        ->get(route('educator.announcements.create'))
        ->assertOk()
        ->assertSee('Aún no tienes un grupo asignado')
        ->assertDontSee('Enviar aviso');
});