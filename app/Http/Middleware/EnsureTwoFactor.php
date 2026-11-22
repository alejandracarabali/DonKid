<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verificación en dos pasos obligatoria (por ahora, para el superadministrador).
 * 1. Si no la ha configurado → a la pantalla de configuración.
 * 2. Si la configuró pero no ha escrito el código en esta sesión → a pedir el código.
 */
class EnsureTwoFactor
{
    public const SESSION_KEY = 'two_factor_passed';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user->hasTwoFactor()) {
            return redirect()->route('platform.two-factor.setup');
        }

        if ($request->session()->get(self::SESSION_KEY) !== $user->id) {
            // Guardar adónde iba, para llevarlo allí después del código.
            $request->session()->put('url.intended', $request->fullUrl());

            return redirect()->route('platform.two-factor.challenge');
        }

        return $next($request);
    }
}
