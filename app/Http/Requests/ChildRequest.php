<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\SchoolRules;
use Illuminate\Foundation\Http\FormRequest;

class ChildRequest extends FormRequest
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
            // El grupo debe existir y ser del colegio de quien envía el formulario.
            'classroom_id' => ['nullable', $this->existsInSchool('classrooms')],
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            // before:today: nadie nace en el futuro.
            'birth_date' => ['required', 'date', 'before:today'],
            'allergies' => ['nullable', 'string', 'max:300'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Escribe el nombre del niño.',
            'last_name.required' => 'Escribe el apellido.',
            'birth_date.required' => 'Elige la fecha de nacimiento.',
            'birth_date.before' => 'La fecha debe ser anterior a hoy.',
            'classroom_id.exists' => 'Ese grupo no existe en tu colegio. Elige otro.',
            'allergies.max' => 'Es muy largo. Máximo 300 letras.',
        ];
    }
}