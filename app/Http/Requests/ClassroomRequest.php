<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolRules;
use Illuminate\Foundation\Http\FormRequest;

class ClassroomRequest extends FormRequest
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
            // Nombre único DENTRO del colegio, ignorando el propio grupo al editar.
            'name' => [
                'required', 'string', 'max:60',
                $this->uniqueInSchool('classrooms', 'name')->ignore($this->route('classroom')),
            ],
            'age_range' => ['nullable', 'string', 'max:40'],
        ];
    }

    // UX Writing: errores humanos y accionables.
    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del grupo.',
            'name.unique' => 'Ya existe un grupo con ese nombre en tu colegio.',
            'name.max' => 'El nombre es muy largo. Máximo 60 letras.',
        ];
    }
}