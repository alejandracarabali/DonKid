<?php

use App\Http\Middleware\EnsureTwoFactor;
use App\Models\User;
use App\Support\Totp;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

// Verificación en dos pasos del superadministrador.

it('sin configurarla, el superadmin no entra a Colegios: lo manda a activarla', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('platform.schools.index'))
        ->assertRedirect(route('platform.two-factor.setup'));
});

it('muestra la clave para la app autenticadora', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('platform.two-factor.setup'))
        ->assertOk()
        ->assertSee('Introducir una clave de configuración')
        ->assertSee('otpauth://totp/', false);
});

it('se activa con un código correcto y muestra 8 códigos de recuperación', function () {
    $user = User::factory()->superAdmin()->create();
    $this->actingAs($user)->get(route('platform.two-factor.setup'));
    $secret = session('two_factor_setup_secret');

    $this->actingAs($user)
        ->post(route('platform.two-factor.confirm'), ['code' => Totp::codeAt($secret, Totp::timeStep())])
        ->assertOk()
        ->assertSee('Guarda estos códigos de recuperación');

    $user->refresh();

    expect($user->hasTwoFactor())->toBeTrue()
        ->and($user->two_factor_secret)->toBe($secret)
        ->and($user->two_factor_recovery_codes)->toHaveCount(8);

    $this->actingAs($user)->get(route('platform.schools.index'))->assertOk();
});

it('no se activa con un código incorrecto', function () {
    $user = User::factory()->superAdmin()->create();
    $this->actingAs($user)->get(route('platform.two-factor.setup'));

    $this->actingAs($user)->post(route('platform.two-factor.confirm'), ['code' => '000000'])
        ->assertSessionHasErrors('code');

    expect($user->fresh()->hasTwoFactor())->toBeFalse();
});

it('guarda la clave cifrada en la base de datos', function () {
    $user = User::factory()->superAdmin()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();

    $raw = DB::table('users')->where('id', $user->id)->value('two_factor_secret');

    expect($raw)->not->toContain('JBSWY3DP');
});

it('al iniciar sesión pide el código antes de entrar', function () {
    $user = User::factory()->superAdmin()->withTwoFactor()->create();

    $this->actingAs($user)
        ->get(route('platform.schools.index'))
        ->assertRedirect(route('platform.two-factor.challenge'));
});

it('con el código correcto entra y vuelve adonde iba', function () {
    $secret = Totp::generateSecret();
    $user = User::factory()->superAdmin()->withTwoFactor($secret)->create();

    $this->actingAs($user)->get(route('platform.schools.create')); // guarda el destino
    $this->actingAs($user)
        ->post(route('platform.two-factor.verify'), ['code' => Totp::codeAt($secret, Totp::timeStep())])
        ->assertRedirect(route('platform.schools.create'));

    expect(session(EnsureTwoFactor::SESSION_KEY))->toBe($user->id);
});

it('no acepta el mismo código dos veces', function () {
    Carbon::setTestNow('2026-11-22 12:00:00');
    $secret = Totp::generateSecret();
    $user = User::factory()->superAdmin()->withTwoFactor($secret)->create();
    $code = Totp::codeAt($secret, Totp::timeStep());

    $this->actingAs($user)->post(route('platform.two-factor.verify'), ['code' => $code])->assertSessionHasNoErrors();

    $this->flushSession();
    $this->actingAs($user->fresh())->post(route('platform.two-factor.verify'), ['code' => $code])
        ->assertSessionHasErrors('code');
});

it('un código de recuperación sirve una sola vez', function () {
    $user = User::factory()->superAdmin()->withTwoFactor()->create();

    $this->actingAs($user)->post(route('platform.two-factor.verify'), ['code' => 'aaaa-bbbb'])
        ->assertSessionHasNoErrors();

    expect($user->fresh()->two_factor_recovery_codes)->toBe(['CCCC-DDDD']);

    $this->flushSession();
    $this->actingAs($user->fresh())->post(route('platform.two-factor.verify'), ['code' => 'AAAA-BBBB'])
        ->assertSessionHasErrors('code');
});

it('limita los intentos a 5 por minuto', function () {
    $user = User::factory()->superAdmin()->withTwoFactor()->create();

    foreach (range(1, 5) as $i) {
        $this->actingAs($user)->post(route('platform.two-factor.verify'), ['code' => '000000']);
    }

    $this->actingAs($user)->post(route('platform.two-factor.verify'), ['code' => '000000'])->assertStatus(429);
});

it('el comando de consola la desactiva para volver a configurarla', function () {
    $user = User::factory()->superAdmin()->withTwoFactor()->create(['email' => 'equipo@donkid.co']);

    $this->artisan('donkid:reset-2fa', ['email' => 'equipo@donkid.co'])->assertSuccessful();

    expect($user->fresh()->hasTwoFactor())->toBeFalse()
        ->and($user->fresh()->two_factor_secret)->toBeNull();
});

it('no afecta a coordinación, docentes ni familias', function (string $role, string $route) {
    $this->actingAs(User::factory()->{$role}()->create())->get(route($route))->assertOk();
})->with([
    ['admin', 'admin.dashboard'],
    ['educator', 'educator.activities.index'],
    ['guardian', 'guardian.dashboard'],
]);
