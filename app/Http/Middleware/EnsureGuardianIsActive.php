<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RF-53: el acudiente de un niño retirado pierde el acceso,
 * salvo que tenga otro hijo activo en la institución.
 *
 * Una cuenta de familia SIN niños vinculados todavía (recién creada) no se bloquea:
 * solo se corta cuando tiene vínculos y TODOS sus niños están retirados.
 */
class EnsureGuardianIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === Role::Guardian && $this->onlyHasWithdrawnChildren($user)) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'status',
                'Tu cuenta ya no está activa porque no tienes estudiantes matriculados. '
                .'Si necesitas un certificado, escríbele a coordinación.'
            );
        }

        return $next($request);
    }

    private function onlyHasWithdrawnChildren($user): bool
    {
        $linked = $user->children()->withoutGlobalScope('active')->count();

        return $linked > 0 && $user->children()->count() === 0;
    }
}
