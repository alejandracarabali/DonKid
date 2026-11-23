<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChildController extends Controller
{
    public function show(Child $child)
    {
        // Si la Policy dice false, Laravel responde 403 automáticamente.
        Gate::authorize('view', $child);

        return view('guardian.children.show', compact('child'));
    }

        // Autorizar o retirar el uso de imagen del niño (fotos de las actividades).
    public function imageConsent(Request $request, Child $child): RedirectResponse
    {
        Gate::authorize('view', $child);

        $request->validate(['consent' => ['required', 'boolean']]);

        if ($request->boolean('consent')) {
            $child->grantImageConsent($request->user(), $request->ip());
            $status = "Listo, autorizaste las fotos de {$child->first_name}.";
        } else {
            $child->revokeImageConsent();
            $status = "Listo, ya no se compartirán fotos de {$child->first_name}.";
        }

    return back()->with('status', $status);    }
}