<?php

namespace App\Http\Requests;

use App\Enums\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenewalRequest extends FormRequest
{
    // El permiso (que sea su hijo y que la renovación esté abierta) lo revisa el controlador.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $healthRequired = config('privacy.health_documents_required');

        return [
            'next_level' => ['required', Rule::enum(GradeLevel::class)->only(GradeLevel::forSchool($this->user()->school?->type))],
            'guardian_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/'],
            'guardian_address' => ['required', 'string', 'max:200'],

            // Ficha de salud: solo se guarda si la familia autoriza los datos de salud.
            'health.eps' => ['nullable', 'string', 'max:80', 'prohibited_unless:consent_health,1'],
            'health.conditions' => ['nullable', 'string', 'max:500', 'prohibited_unless:consent_health,1'],
            'health.medications' => ['nullable', 'string', 'max:500', 'prohibited_unless:consent_health,1'],
            'health.emergency_contact_name' => ['nullable', 'string', 'max:80', 'prohibited_unless:consent_health,1'],
            'health.emergency_contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/', 'prohibited_unless:consent_health,1'],

            'consent_data' => ['accepted'],
            'consent_health' => $healthRequired ? ['accepted'] : ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'next_level.required' => 'Elige el nivel para el próximo año.',
            'next_level.enum' => 'Ese nivel no existe en el colegio. Elige otro.',
            'guardian_phone.required' => 'Escribe un teléfono de contacto.',
            'guardian_phone.regex' => 'Revisa el teléfono: solo números, mínimo 7.',
            'guardian_address.required' => 'Escribe tu dirección.',
            'health.emergency_contact_phone.regex' => 'Revisa el teléfono de emergencia: solo números, mínimo 7.',
            'health.*.prohibited_unless' => 'Para guardar datos de salud, marca la autorización de datos de salud.',
            'consent_data.accepted' => 'Para renovar debes aceptar la autorización de tratamiento de datos.',
            'consent_health.accepted' => 'Para la matrícula necesitamos tu autorización para los datos de salud.',
        ];
    }
}
