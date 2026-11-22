<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Support\MessagingHours;
use App\Support\Navigation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// RF-27 y RF-62: mensajes entre la familia y el director de grupo.

afterEach(fn () => Carbon::setTestNow());

// Hora local de Colombia para los tests.
function bogota(string $time): Carbon
{
    return Carbon::parse($time, 'America/Bogota');
}

function familyAndDirector(): array
{
    $grupo = Classroom::factory()->create();
    $director = User::factory()->educator()->create(['name' => 'Sol Parra']);
    $materia = User::factory()->educator()->create();
    $grupo->educators()->attach([$director->id, $materia->id]);
    $grupo->update(['director_id' => $director->id]);
    $nino = Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ana']);
    $madre = User::factory()->guardian()->create(['name' => 'Marta Gómez']);
    $madre->children()->attach($nino);

    return [$madre, $nino, $director, $materia];
}

describe('horario de entrega', function () {
    it('dentro del horario se entrega de inmediato', function () {
        $now = bogota('2026-11-23 10:15');
        expect(MessagingHours::deliveryTime($now)->equalTo($now))->toBeTrue();
    });

    it('de noche se entrega al día siguiente a las 6:00 a. m.', function () {
        expect(MessagingHours::deliveryTime(bogota('2026-11-23 21:30'))->equalTo(bogota('2026-11-24 06:00')))->toBeTrue()
            ->and(MessagingHours::deliveryTime(bogota('2026-11-23 19:00'))->equalTo(bogota('2026-11-24 06:00')))->toBeTrue();
    });

    it('de madrugada se entrega ese mismo día a las 6:00 a. m.', function () {
        expect(MessagingHours::deliveryTime(bogota('2026-11-24 04:10'))->equalTo(bogota('2026-11-24 06:00')))->toBeTrue();
    });
});

it('la familia escribe al director dentro del horario y le llega de inmediato', function () {
    Carbon::setTestNow(bogota('2026-11-23 10:00'));
    [$madre, $nino, $director] = familyAndDirector();

    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => 'Hoy Ana sale a las 11.'])
        ->assertRedirect(route('guardian.messages.show', $nino));

    $this->actingAs($director)->get(route('educator.messages.index'))
        ->assertOk()->assertSee('Ana')->assertSee('1 nuevo');

    $this->actingAs($director)->get(route('educator.messages.show', Conversation::first()))
        ->assertOk()->assertSee('Hoy Ana sale a las 11.');
});

it('un mensaje de noche no le aparece al director hasta las 6:00 a. m.', function () {
    Carbon::setTestNow(bogota('2026-11-23 21:30'));
    [$madre, $nino, $director] = familyAndDirector();

    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => 'Mañana no va.'])
        ->assertSessionHas('status', fn ($s) => str_contains($s, '6:00 a. m.'));

    $this->actingAs($director)->get(route('educator.messages.index'))->assertDontSee('Ana');
    expect(collect(Navigation::for($director))->firstWhere('label', 'Mensajes')['badge'])->toBe(0);

    Carbon::setTestNow(bogota('2026-11-24 06:00'));
    $this->actingAs($director)->get(route('educator.messages.index'))->assertSee('Ana')->assertSee('1 nuevo');
});

it('la familia ve que su mensaje se entregará a las 6:00 a. m.', function () {
    Carbon::setTestNow(bogota('2026-11-23 22:00'));
    [$madre, $nino] = familyAndDirector();

    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => 'Hola']);

    $this->actingAs($madre)->get(route('guardian.messages.show', $nino))
        ->assertSee('Se entrega a las 6:00 a. m.');
});

it('el director responde y la familia lo ve como mensaje nuevo', function () {
    Carbon::setTestNow(bogota('2026-11-23 10:00'));
    [$madre, $nino, $director] = familyAndDirector();
    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => '¿Cómo estuvo Ana?']);
    $conversation = Conversation::first();

    $this->actingAs($director)->post(route('educator.messages.store', $conversation), ['body' => 'Muy bien, jugó mucho.'])
        ->assertRedirect(route('educator.messages.show', $conversation));

    $madre = $madre->fresh();
    expect(collect(Navigation::for($madre))->firstWhere('label', 'Mensajes')['badge'])->toBe(1);

    $this->actingAs($madre)->get(route('guardian.messages.show', $nino))->assertSee('Muy bien, jugó mucho.');
    expect(collect(Navigation::for($madre))->firstWhere('label', 'Mensajes')['badge'])->toBe(0);
});

it('el docente por materia no ve los mensajes del grupo', function () {
    Carbon::setTestNow(bogota('2026-11-23 10:00'));
    [$madre, $nino, , $materia] = familyAndDirector();
    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => 'Privado']);

    $this->actingAs($materia)->get(route('educator.messages.index'))->assertDontSee('Ana');
    $this->actingAs($materia)->get(route('educator.messages.show', Conversation::first()))->assertForbidden();
});

it('una familia no puede escribir sobre el hijo de otra ni leer su conversación', function () {
    Carbon::setTestNow(bogota('2026-11-23 10:00'));
    [$madre, $nino] = familyAndDirector();
    $otra = User::factory()->guardian()->create();
    $otra->children()->attach(Child::factory()->create());

    $this->actingAs($otra)->get(route('guardian.messages.show', $nino))->assertForbidden();
    $this->actingAs($otra)->post(route('guardian.messages.store', $nino), ['body' => 'x'])->assertForbidden();
});

it('guarda los mensajes cifrados', function () {
    Carbon::setTestNow(bogota('2026-11-23 10:00'));
    [$madre, $nino] = familyAndDirector();

    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => 'Ana tiene fiebre']);

    expect(DB::table('messages')->value('body'))->not->toContain('fiebre')
        ->and(Message::first()->body)->toBe('Ana tiene fiebre');
});

it('exige el texto del mensaje', function () {
    [$madre, $nino] = familyAndDirector();

    $this->actingAs($madre)->post(route('guardian.messages.store', $nino), ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('la familia ve quién es el director en la lista de mensajes', function () {
    [$madre] = familyAndDirector();

    $this->actingAs($madre)->get(route('guardian.messages.index'))
        ->assertOk()->assertSee('Director de grupo: Sol Parra');
});

it('la docente tiene Mensajes en la barra en lugar de Perfil', function () {
    [, , $director] = familyAndDirector();

    $labels = collect(Navigation::for($director))->pluck('label');

    expect($labels)->toContain('Mensajes')->not->toContain('Perfil');
});
