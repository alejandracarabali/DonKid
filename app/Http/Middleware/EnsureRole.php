<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    // ...$roles recibe los parámetros de la ruta:
    // 'role:admin,educator' llega como ['admin', 'educator']
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Si no hay usuario o su rol no está en la lista, cortamos con 403.
        // 403 = "sé quién eres, pero no tienes permiso".
        abort_unless(
            $user && in_array($user->role->value, $roles, true),
            403,
            'Esta sección no está disponible para tu cuenta.'
        );

        // Si pasó el control, la petición sigue hacia el controlador.
        return $next($request);
    }
}