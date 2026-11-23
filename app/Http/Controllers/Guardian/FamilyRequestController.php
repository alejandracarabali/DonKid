<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\FamilyRequestType;
use App\Http\Controllers\Controller;
use App\Http\Requests\FamilyRequestRequest;
use App\Models\Child;
use App\Models\FamilyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Fase 5: la familia avisa al colegio con pocos toques (excusa, permiso, recomendación).
 */
class FamilyRequestController extends Controller
{
    public function index(Request $request): View
    {
        return view('guardian.requests.index', [
            'children' => $request->user()->children()->orderBy('first_name')->get(),
        ]);
    }

    public function child(Request $request, Child $child): View
    {
        Gate::authorize('view', $child);

        return view('guardian.requests.child', [
            'child' => $child,
            'types' => FamilyRequestType::cases(),
            'sent' => FamilyRequest::where('child_id', $child->id)
                ->where('guardian_id', $request->user()->id)->latest()->limit(20)->get(),
        ]);
    }

    public function create(Child $child, string $type): View
    {
        Gate::authorize('view', $child);
        $type = FamilyRequestType::tryFrom($type) ?? abort(404);

        return view('guardian.requests.create', compact('child', 'type'));
    }

    public function store(FamilyRequestRequest $request, Child $child): RedirectResponse
    {
        Gate::authorize('view', $child);

        $data = $request->validated();
        $familyRequest = new FamilyRequest(collect($data)->except('type')->all());
        $familyRequest->forceFill([
            'type' => $data['type'],
            'child_id' => $child->id,
            'guardian_id' => $request->user()->id,
        ]);

        // Permiso para que recoja otra persona: código de 4 dígitos que esa persona dirá al recoger.
        if ($familyRequest->isPickupByOther()) {
            $familyRequest->pickup_code = (string) random_int(1000, 9999);
        }
        $familyRequest->save();

        return redirect()->route('guardian.requests.child', $child)
            ->with('status', 'Listo, el colegio ya recibió tu solicitud.')
            ->with('pickup_code', $familyRequest->pickup_code);
    }
}
