<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Support\Facades\Gate;

class ChildController extends Controller
{
    public function show(Child $child)
    {
        // Si la Policy dice false, Laravel responde 403 automáticamente.
        Gate::authorize('view', $child);

        return view('guardian.children.show', compact('child'));
    }
}