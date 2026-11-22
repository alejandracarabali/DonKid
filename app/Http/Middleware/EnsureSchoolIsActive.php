<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * RF-56: si el colegio está suspendido, nadie de ese colegio puede usar DonKid.
 * Los datos se conservan; al reactivarlo, todo vuelve a funcionar.
 */
class EnsureSchoolIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->school?->isSuspended()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with(
                'status',
                'El acceso a DonKid de tu colegio está suspendido. Comunícate con la coordinación de tu colegio.'
            );
        }

        return $next($request);
    }
}
