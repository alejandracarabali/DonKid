<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ApproveEnrollment;
use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\EnrollmentApplication;
use App\Models\EnrollmentDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * RF-35: bandeja de inscripciones de coordinación.
 * BelongsToSchool en EnrollmentApplication: coordinación solo ve las de su colegio
 * (una de otro colegio responde 404).
 */
class EnrollmentController extends Controller
{
    public function index(Request $request): View
    {
        $status = EnrollmentStatus::tryFrom($request->query('estado', 'pending')) ?? EnrollmentStatus::Pending;

        return view('admin.enrollments.index', [
            'status' => $status,
            'applications' => EnrollmentApplication::where('status', $status)->latest()->paginate(15)->withQueryString(),
            'counts' => EnrollmentApplication::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }

    public function show(EnrollmentApplication $application): View
    {
        return view('admin.enrollments.show', [
            'application' => $application->load('documents', 'child'),
            // Primero los grupos del nivel al que aspira.
            'classrooms' => Classroom::orderByRaw('level = ? desc', [$application->desired_level?->value])->orderBy('name')->get(),
        ]);
    }

    // Abre un documento del disco privado. Solo coordinación, solo de su colegio.
    public function document(EnrollmentApplication $application, EnrollmentDocument $document): StreamedResponse
    {
        abort_unless($document->enrollment_application_id === $application->id, 404);

        return Storage::disk('local')->response($document->path, $document->original_name, [
            'Content-Type' => $document->mime_type,
            'Cache-Control' => 'private, no-store', // que el navegador no lo guarde en caché
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function approve(Request $request, EnrollmentApplication $application, ApproveEnrollment $approve): RedirectResponse
    {
        $this->ensurePending($application);

        $data = $request->validate([
            'classroom_id' => ['required', Rule::exists('classrooms', 'id')->where('school_id', $request->user()->school_id)],
        ], [
            'classroom_id.required' => 'Elige el grupo al que entrará.',
            'classroom_id.exists' => 'Ese grupo no existe en tu colegio. Elige otro.',
        ]);

        $result = $approve->handle($application, Classroom::findOrFail($data['classroom_id']), $request->user());

        $redirect = redirect()->route('admin.enrollments.index')
            ->with('status', "Listo, {$result['child']->first_name} quedó matriculado.");

        // La clave temporal solo se muestra una vez, como al crear familias a mano.
        return $result['temporal']
            ? $redirect->with('temporal_password', ['email' => $result['guardian']->email, 'password' => $result['temporal']])
            : $redirect->with('status', "Listo, {$result['child']->first_name} quedó matriculado y vinculado a la cuenta que su familia ya tenía.");
    }

    public function reject(Request $request, EnrollmentApplication $application): RedirectResponse
    {
        $this->ensurePending($application);

        $data = $request->validate(
            ['rejection_reason' => ['required', 'string', 'max:200']],
            ['rejection_reason.required' => 'Escribe el motivo: lo verá la familia si pregunta.']
        );

        $application->forceFill([
            'status' => EnrollmentStatus::Rejected,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
            'reviewed_by' => $request->user()->id,
        ])->save();

        return redirect()->route('admin.enrollments.index')
            ->with('status', "La solicitud {$application->reference()} quedó rechazada.");
    }

    // Una solicitud ya revisada no se vuelve a aprobar ni a rechazar.
    private function ensurePending(EnrollmentApplication $application): void
    {
        abort_unless($application->status === EnrollmentStatus::Pending, 409, 'Esta solicitud ya fue revisada.');
    }
}
