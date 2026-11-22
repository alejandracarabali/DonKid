<?php

namespace App\Http\Requests;

use App\Enums\GradeLevel;
use App\Enums\Role;
use App\Http\Requests\Concerns\SchoolRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            // RF-54: el nivel define si el grupo lleva bitácora. Solo se aceptan
            // los niveles que corresponden al tipo de institución.
            'level' => ['required', Rule::enum(GradeLevel::class)->only($this->allowedLevels())],
            // RF-61: el director debe ser un docente de este colegio.
            'director_id' => ['nullable', $this->existsInSchool('users')->where('role', Role::Educator->value)],
        ];
    }

    /** @return list<GradeLevel> */
    public function allowedLevels(): array
    {
        return GradeLevel::forSchool($this->user()?->school?->type);
    }

    // UX Writing: errores humanos y accionables.
    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del grupo.',
            'name.unique' => 'Ya existe un grupo con ese nombre en tu colegio.',
            'name.max' => 'El nombre es muy largo. Máximo 60 letras.',
            'level.required' => 'Elige el nivel del grupo.',
            'level.enum' => 'Ese nivel no existe en tu institución. Elige otro.',
            'director_id.exists' => 'Elige un docente de tu colegio como director de grupo.',
        ];
    }
}