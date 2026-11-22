<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureTwoFactor;
use App\Support\Totp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Verificación en dos pasos con app autenticadora (TOTP).
 */
class TwoFactorController extends Controller
{
    // Paso 1: mostrar la clave para agregarla a la app autenticadora.
    public function setup(Request $request): View|RedirectResponse
    {
        if ($request->user()->hasTwoFactor()) {
            return redirect()->route('platform.schools.index');
        }

        // La clave vive en la sesión hasta que se confirme con un código correcto.
        $secret = $request->session()->get('two_factor_setup_secret') ?? Totp::generateSecret();
        $request->session()->put('two_factor_setup_secret', $secret);

        return view('platform.two-factor.setup', [
            'secret' => Totp::format($secret),
            'uri' => Totp::uri($secret, $request->user()->email),
        ]);
    }

    // Paso 2: confirmar con un código de la app. Solo así se activa.
    public function confirm(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $secret = $request->session()->get('two_factor_setup_secret');
        abort_if($user->hasTwoFactor() || ! $secret, 409);

        $request->validate(['code' => ['required', 'string']], ['code.required' => 'Escribe el código de 6 dígitos.']);

        $step = Totp::verify($secret, $request->input('code'));
        if ($step === null) {
            throw ValidationException::withMessages(['code' => 'El código no coincide. Revisa que la hora de tu celular esté bien.']);
        }

        $codes = $this->recoveryCodes();
        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $codes,
            'two_factor_confirmed_at' => now(),
            'two_factor_last_step' => $step,
        ])->save();

        $request->session()->forget('two_factor_setup_secret');
        $request->session()->put(EnsureTwoFactor::SESSION_KEY, $user->id);

        // Los códigos de recuperación se muestran una sola vez.
        return view('platform.two-factor.recovery-codes', ['codes' => $codes]);
    }

    // Cada vez que inicia sesión: pedir el código.
    public function challenge(Request $request): View|RedirectResponse
    {
        if (! $request->user()->hasTwoFactor()) {
            return redirect()->route('platform.two-factor.setup');
        }

        return view('platform.two-factor.challenge');
    }

    public function verify(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->hasTwoFactor(), 409);

        $request->validate(['code' => ['required', 'string', 'max:20']], ['code.required' => 'Escribe el código.']);
        $input = trim($request->input('code'));

        $step = Totp::verify($user->two_factor_secret, $input, $user->two_factor_last_step);

        if ($step !== null) {
            $user->forceFill(['two_factor_last_step' => $step])->save();
        } elseif (! $this->useRecoveryCode($user, $input)) {
            throw ValidationException::withMessages(['code' => 'El código no es válido o ya se usó.']);
        }

        $request->session()->regenerate(); // nueva sesión tras el segundo paso
        $request->session()->put(EnsureTwoFactor::SESSION_KEY, $user->id);

        return redirect()->intended(route('platform.schools.index'));
    }

    // Un código de recuperación sirve una sola vez: al usarlo se borra.
    private function useRecoveryCode($user, string $input): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $normalized = strtoupper(str_replace(' ', '', $input));

        foreach ($codes as $i => $code) {
            if (hash_equals($code, $normalized)) {
                unset($codes[$i]);
                $user->forceFill(['two_factor_recovery_codes' => array_values($codes)])->save();

                return true;
            }
        }

        return false;
    }

    /** @return list<string> 8 códigos como "K7QD-M2XP" */
    private function recoveryCodes(): array
    {
        return collect(range(1, 8))
            ->map(fn () => strtoupper(Str::random(4).'-'.Str::random(4)))
            ->all();
    }
}
