<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Enums\GradeLevel;
use App\Http\Requests\EnrollmentApplicationRequest;
use App\Models\EnrollmentApplication;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * RF-34: formulario público de inscripción, sin iniciar sesión.
 * La familia llega con el enlace del colegio: /inscripcion/{slug-del-colegio}.
 */
class EnrollmentApplicationController extends Controller
{
    public function create(School $school): View
    {
        return view('enrollment.create', [
            'school' => $school,
            'levels' => GradeLevel::forSchool($school->type),
            'documentTypes' => DocumentType::cases(),
            'relationships' => ['Madre', 'Padre', 'Abuela/o', 'Tía/o', 'Otro'],
        ]);
    }

    public function store(EnrollmentApplicationRequest $request, School $school): RedirectResponse
    {
        $application = new EnrollmentApplication($request->safe()->except('website'));
        // El colegio sale de la URL, nunca del formulario: así nadie la envía a otro colegio.
        $application->school_id = $school->id;
        $application->save();

        return redirect()
            ->route('enrollment.sent', $school)
            ->with('reference', $application->reference());
    }

    public function sent(School $school): View|RedirectResponse
    {
        // Sin referencia en la sesión (alguien abrió la URL directo): de vuelta al formulario.
        if (! session()->has('reference')) {
            return redirect()->route('enrollment.create', $school);
        }

        return view('enrollment.sent', [
            'school' => $school,
            'reference' => session('reference'),
        ]);
    }
}
