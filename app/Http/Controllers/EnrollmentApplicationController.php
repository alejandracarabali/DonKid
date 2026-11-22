<?php

namespace App\Http\Controllers;

use App\Enums\DocumentType;
use App\Enums\EnrollmentDocumentType;
use App\Enums\GradeLevel;
use App\Http\Requests\EnrollmentApplicationRequest;
use App\Models\EnrollmentApplication;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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
            'enrollmentDocuments' => EnrollmentDocumentType::cases(),
        ]);
    }

    public function store(EnrollmentApplicationRequest $request, School $school): RedirectResponse
    {
        $stored = [];

        try {
            // Transacción: si algo falla, no queda una solicitud a medias en la base de datos.
            $application = DB::transaction(function () use ($request, $school, &$stored) {
                $application = new EnrollmentApplication(
                    $request->safe()->except(['website', 'documents', 'consent_data', 'consent_health'])
                );
                // El colegio sale de la URL, nunca del formulario: así nadie la envía a otro colegio.
                $application->school_id = $school->id;

                // RF-37: prueba de la autorización. forceFill porque estos campos no son asignables
                // desde el formulario: los pone el servidor.
                $application->forceFill([
                    'data_consent_at' => now(),
                    'health_consent_at' => $request->boolean('consent_health') ? now() : null,
                    'consent_ip' => $request->ip(),
                    'consent_policy_version' => config('privacy.policy_version'),
                ]);
                $application->save();

                foreach (EnrollmentDocumentType::cases() as $type) {
                    $file = $request->file("documents.{$type->value}");
                    if (! $file) {
                        continue; // documento opcional que no se adjuntó
                    }
                    // Nombre aleatorio en el disco PRIVADO: el nombre original no se usa como ruta.
                    $path = $file->storeAs(
                        "inscripciones/{$application->id}",
                        $type->value.'-'.Str::uuid().'.'.$file->extension(),
                        'local'
                    );
                    $stored[] = $path;

                    $application->documents()->create([
                        'type' => $type,
                        'path' => $path,
                        'original_name' => Str::limit($file->getClientOriginalName(), 195),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }

                return $application;
            });
        } catch (\Throwable $e) {
            // La transacción deshace la base de datos, pero los archivos ya escritos hay que borrarlos a mano.
            Storage::disk('local')->delete($stored);
            throw $e;
        }

        return redirect()
            ->route('enrollment.sent', $school)
            ->with('reference', $application->reference());
    }

    // Política de tratamiento de datos del colegio (enlazada desde la autorización).
    public function policy(School $school): View
    {
        return view('enrollment.policy', ['school' => $school]);
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
