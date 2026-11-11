<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        // Si la cuenta aún usa la clave temporal, solo puede ir a cambiarla.
        if ($request->user()?->must_change_password) {
            return redirect()->route('password.force');
        }

        return $next($request);
    }
}