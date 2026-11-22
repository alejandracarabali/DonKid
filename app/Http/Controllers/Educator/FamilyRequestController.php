<?php

namespace App\Http\Controllers\Educator;

use App\Enums\FamilyRequestStatus;
use App\Enums\FamilyRequestType;
use App\Http\Controllers\Controller;
use App\Models\FamilyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

/**
 * El director de grupo marca la solicitud como vista (excusa, recomendación) o confirma el permiso.
 */
class FamilyRequestController extends Controller
{
    public function handle(FamilyRequest $familyRequest): RedirectResponse
    {
        Gate::authorize('handle', $familyRequest);

        $familyRequest->forceFill([
            'status' => $familyRequest->type === FamilyRequestType::Permission
                ? FamilyRequestStatus::Confirmed
                : FamilyRequestStatus::Seen,
            'handled_by' => auth()->id(),
            'handled_at' => now(),
        ])->save();

        return back()->with('status', "Listo, la familia de {$familyRequest->child->first_name} ya ve que lo recibiste.");
    }
}
