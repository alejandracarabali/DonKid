<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EducatorRequest extends FormRequest
{
    use SchoolRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            // El correo sigue siendo único en TODA la plataforma: identifica la cuenta al iniciar sesión.
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('users', 'email')->ignore($this->route('educator')),
            ],
            'classrooms' => ['nullable', 'array'],
            'classrooms.*' => [$this->existsInSchool('classrooms')],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre de la educadora.',
            'email.required' => 'Escribe un correo, por ejemplo: ana@correo.com',
            'email.email' => 'Ese correo no parece válido. Revisa que tenga @.',
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'classrooms.*.exists' => 'Uno de los grupos elegidos no existe en tu colegio.',
        ];
    }
}