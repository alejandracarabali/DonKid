<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
use App\Enums\EnrollmentDocumentType;
use App\Enums\GradeLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EnrollmentApplicationRequest extends FormRequest
{
    // Formulario público: cualquiera puede enviarlo (lo protegen el límite de envíos y la trampa anti-bots).
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $school = $this->route('school');

        return [
            // Trampa anti-bots: campo oculto que una persona nunca llena.
            'website' => ['prohibited'],

            'child_first_name' => ['required', 'string', 'max:60'],
            'child_last_name' => ['required', 'string', 'max:60'],
            // Entre 0 y 12 años: párvulos a 5.° de primaria.
            'child_birth_date' => ['required', 'date', 'before:today', 'after:'.now()->subYears(12)->toDateString()],
            'desired_level' => ['required', Rule::enum(GradeLevel::class)->only(GradeLevel::forSchool($school->type))],

            'guardian_name' => ['required', 'string', 'max:80'],
            'guardian_document_type' => ['required', Rule::enum(DocumentType::class)],
            'guardian_document_number' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9-]{4,20}$/'],
            'guardian_relationship' => ['required', Rule::in(['Madre', 'Padre', 'Abuela/o', 'Tía/o', 'Otro'])],
            'guardian_email' => ['required', 'email', 'max:120'],
            'guardian_phone' => ['required', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/'],
            'guardian_address' => ['required', 'string', 'max:200'],

            // RF-37: autorización general (siempre obligatoria) y de datos de salud.
            'consent_data' => ['accepted'],
            'consent_image' => ['nullable', 'boolean'],            
            'consent_health' => config('privacy.health_documents_required')
                ? ['accepted']
                : ['nullable', 'boolean'],
        ] + $this->documentRules();
    }

    // Los 4 documentos son obligatorios. Máximo 4 MB cada uno.
    private function documentRules(): array
    {
        $rules = [];
        foreach (EnrollmentDocumentType::cases() as $type) {
            $rules["documents.{$type->value}"] = [
                $type->isRequired() ? 'required' : 'nullable',
                'file', 'mimes:'.implode(',', $type->mimes()), 'max:4096',
            ];

            // Un documento de salud solo se acepta si la familia autorizó los datos sensibles.
            if ($type->isHealth()) {
                $rules["documents.{$type->value}"][] = 'prohibited_unless:consent_health,1';
            }
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'No pudimos enviar la solicitud. Inténtalo de nuevo.',
            'child_first_name.required' => 'Escribe el nombre del niño o niña.',
            'child_last_name.required' => 'Escribe el apellido.',
            'child_birth_date.required' => 'Elige la fecha de nacimiento.',
            'child_birth_date.before' => 'La fecha debe ser anterior a hoy.',
            'child_birth_date.after' => 'Revisa la fecha: atendemos niños de hasta 12 años.',
            'desired_level.required' => 'Elige el nivel al que aspira.',
            'desired_level.enum' => 'Ese nivel no existe en este colegio. Elige otro.',
            'guardian_name.required' => 'Escribe tu nombre completo.',
            'guardian_document_type.required' => 'Elige el tipo de documento.',
            'guardian_document_number.required' => 'Escribe el número de documento.',
            'guardian_document_number.regex' => 'Revisa el número de documento: solo letras, números y guiones.',
            'guardian_relationship.required' => 'Elige el parentesco.',
            'guardian_email.required' => 'Escribe tu correo.',
            'guardian_email.email' => 'Revisa el correo: parece incompleto.',
            'guardian_phone.required' => 'Escribe un teléfono de contacto.',
            'guardian_phone.regex' => 'Revisa el teléfono: solo números, mínimo 7.',
            'guardian_address.required' => 'Escribe tu dirección.',
            'consent_data.accepted' => 'Para enviar la solicitud debes aceptar la autorización de tratamiento de datos.',
            'consent_health.accepted' => 'Para la matrícula necesitamos tu autorización para los datos de salud.',
        ] + $this->documentMessages();
    }

    private function documentMessages(): array
    {
        $messages = [];
        foreach (EnrollmentDocumentType::cases() as $type) {
            $key = "documents.{$type->value}";
            $formats = strtoupper(implode(', ', array_diff($type->mimes(), ['jpeg'])));
            $messages["{$key}.required"] = "Adjunta: {$type->label()}.";
            $messages["{$key}.uploaded"] = "No se pudo subir: {$type->label()}. Prueba con un archivo más liviano.";
            $messages["{$key}.mimes"] = "{$type->label()}: sube un archivo {$formats}.";
            $messages["{$key}.max"] = "{$type->label()}: el archivo pesa más de 4 MB.";
            $messages["{$key}.prohibited_unless"] = "Para adjuntar {$type->label()}, marca la autorización de datos de salud.";
        }

        return $messages;
    }
}
