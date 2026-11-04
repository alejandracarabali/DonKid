<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EducatorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('users', 'email')->ignore($this->route('educator')),
            ],
            'classrooms' => ['nullable', 'array'],
            'classrooms.*' => ['exists:classrooms,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre de la educadora.',
            'email.required' => 'Escribe un correo, por ejemplo: ana@correo.com',
            'email.email' => 'Ese correo no parece válido. Revisa que tenga @.',
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'classrooms.*.exists' => 'Uno de los grupos elegidos ya no existe.',
        ];
    }
}