<?php

namespace App\Http\Requests;

use App\Enums\DocumentType;
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
        ];
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
        ];
    }
}
