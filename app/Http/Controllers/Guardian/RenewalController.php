<?php

namespace App\Http\Controllers\Guardian;

use App\Enums\GradeLevel;
use App\Http\Controllers\Controller;
use App\Http\Requests\RenewalRequest;
use App\Models\Child;
use App\Models\EnrollmentApplication;
use App\Models\EnrollmentRenewal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * RF-36: la familia renueva la matrícula de su hijo para el año siguiente,
 * revisando los datos que ya tenemos y cambiando solo lo necesario.
 */
class RenewalController extends Controller
{
    public function create(Request $request, Child $child): View|RedirectResponse
    {
        $year = $this->ensureCanRenew($request, $child);

        if ($child->renewals()->where('year', $year)->exists()) {
            return redirect()->route('guardian.dashboard')
                ->with('status', "La matrícula {$year} de {$child->first_name} ya está renovada.");
        }

        // Prellenar con lo último que sabemos: renovación anterior o solicitud de inscripción.
        $last = $child->renewals()->latest('year')->first()
            ?? EnrollmentApplication::where('child_id', $child->id)->first();

        $current = $child->classroom?->level;

        return view('guardian.renewals.create', [
            'child' => $child->load('healthRecord'),
            'year' => $year,
            'levels' => GradeLevel::forSchool($request->user()->school?->type),
            'suggestedLevel' => $current?->next() ?? $current,
            'phone' => $last?->guardian_phone,
            'address' => $last?->guardian_address,
        ]);
    }

    public function store(RenewalRequest $request, Child $child): RedirectResponse
    {
        $year = $this->ensureCanRenew($request, $child);
        abort_if($child->renewals()->where('year', $year)->exists(), 409, 'Esta matrícula ya está renovada.');

        $data = $request->validated();

        DB::transaction(function () use ($request, $child, $year, $data) {
            $renewal = new EnrollmentRenewal(collect($data)->only(['next_level', 'guardian_phone', 'guardian_address'])->all());
            $renewal->forceFill([
                'child_id' => $child->id,
                'guardian_id' => $request->user()->id,
                'year' => $year,
                'data_consent_at' => now(),
                'health_consent_at' => $request->boolean('consent_health') ? now() : null,
                'consent_ip' => $request->ip(),
                'consent_policy_version' => config('privacy.policy_version'),
            ])->save();

            // La familia actualiza la ficha de salud de su hijo, solo si autorizó esos datos.
            if ($request->boolean('consent_health')) {
                $child->healthRecord()->updateOrCreate([], $data['health'] ?? []);
            }
        });

        return redirect()->route('guardian.dashboard')
            ->with('status', "Listo, la matrícula {$year} de {$child->first_name} quedó renovada.");
    }

    // Que sea su hijo, que haya renovación abierta y que no termine primaria. Devuelve el año.
    private function ensureCanRenew(Request $request, Child $child): int
    {
        Gate::authorize('view', $child);

        $school = $request->user()->school;
        abort_unless($school?->renewalOpen(), 404);
        abort_if($child->classroom?->level === GradeLevel::Quinto, 404);

        return $school->renewal_year;
    }
}
