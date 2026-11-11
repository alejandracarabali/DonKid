<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianRequest extends FormRequest
{
    use SchoolRules;

    // La zona ya está protegida por el middleware role:admin.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            // Correo único en toda la plataforma, ignorando a la propia familia al editar.
            'email' => [
                'required', 'email', 'max:120',
                Rule::unique('users', 'email')->ignore($this->route('guardian')),
            ],
            // Niños vinculados: cada id debe existir y ser del mismo colegio.
            'children' => ['nullable', 'array'],
            'children.*' => [$this->existsInSchool('children')],
            // Parentesco por niño: relationships[ID_DEL_NIÑO] = "Mamá"
            'relationships' => ['nullable', 'array'],
            'relationships.*' => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre de la persona.',
            'email.required' => 'Escribe un correo, por ejemplo: ana@correo.com',
            'email.email' => 'Ese correo no parece válido. Revisa que tenga @.',
            'email.unique' => 'Ya hay una cuenta con ese correo.',
            'children.*.exists' => 'Uno de los niños elegidos no existe en tu colegio.',
        ];
    }
}