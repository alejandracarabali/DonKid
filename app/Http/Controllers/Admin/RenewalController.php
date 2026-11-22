<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * RF-36: coordinación abre y cierra la renovación, y ve quién ya renovó.
 */
class RenewalController extends Controller
{
    public function index(Request $request): View
    {
        $year = $request->user()->school?->renewal_year;

        $children = Child::with(['classroom', 'renewals' => fn ($q) => $q->where('year', $year)])
            ->orderBy('first_name')
            ->get();

        return view('admin.renewals.index', [
            'year' => $year,
            'children' => $children,
            'renewed' => $children->filter(fn (Child $c) => $c->renewals->isNotEmpty())->count(),
        ]);
    }

    public function open(Request $request): RedirectResponse
    {
        $data = $request->validate(['year' => ['required', 'integer', 'min:'.now()->year, 'max:'.(now()->year + 1)]]);

        $request->user()->school->forceFill(['renewal_year' => $data['year']])->save();

        return redirect()->route('admin.renewals.index')
            ->with('status', "Listo, las familias ya pueden renovar la matrícula {$data['year']}.");
    }

    public function close(Request $request): RedirectResponse
    {
        $request->user()->school->forceFill(['renewal_year' => null])->save();

        return redirect()->route('admin.renewals.index')
            ->with('status', 'La renovación quedó cerrada. Las renovaciones recibidas se conservan.');
    }
}
