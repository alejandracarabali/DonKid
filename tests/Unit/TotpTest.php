<?php

use App\Support\Totp;

// Vectores oficiales del RFC 6238 (SHA-1, clave "12345678901234567890").
// Si estos pasan, los códigos coinciden con cualquier app autenticadora.
it('calcula los códigos del estándar RFC 6238', function (int $timestamp, string $expected) {
    $secret = Totp::base32Encode('12345678901234567890');

    expect(Totp::codeAt($secret, Totp::timeStep($timestamp)))->toBe($expected);
})->with([
    [59, '287082'],
    [1111111109, '081804'],
    [1111111111, '050471'],
    [1234567890, '005924'],
    [2000000000, '279037'],
]);

it('codifica y decodifica base32 sin perder información', function () {
    $bytes = random_bytes(20);

    expect(Totp::base32Decode(Totp::base32Encode($bytes)))->toBe($bytes);
});

it('acepta el código actual y los de ±30 segundos, y nada más', function () {
    $secret = Totp::generateSecret();
    $now = 1_800_000_000;
    $step = Totp::timeStep($now);

    expect(Totp::verify($secret, Totp::codeAt($secret, $step), null, $now))->toBe($step)
        ->and(Totp::verify($secret, Totp::codeAt($secret, $step - 1), null, $now))->toBe($step - 1)
        ->and(Totp::verify($secret, Totp::codeAt($secret, $step + 1), null, $now))->toBe($step + 1)
        ->and(Totp::verify($secret, Totp::codeAt($secret, $step - 2), null, $now))->toBeNull();
});

it('no deja reusar un código ya usado', function () {
    $secret = Totp::generateSecret();
    $now = 1_800_000_000;
    $code = Totp::codeAt($secret, Totp::timeStep($now));

    $used = Totp::verify($secret, $code, null, $now);

    expect(Totp::verify($secret, $code, $used, $now))->toBeNull();
});

it('rechaza códigos con formato inválido', function () {
    expect(Totp::verify(Totp::generateSecret(), 'abc123'))->toBeNull()
        ->and(Totp::verify(Totp::generateSecret(), '12345'))->toBeNull();
});
