<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActivityRequest extends FormRequest
{
    // La autorización real está en la policy (el controlador usa Gate).
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['required', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'classroom_id.required' => 'Elige el grupo de la actividad.',
            'title.required' => 'Escribe un título para la actividad.',
            'starts_at.required' => 'Indica cuándo empieza.',
            'ends_at.required' => 'Indica cuándo termina.',
            'ends_at.after' => 'La hora de fin debe ser posterior a la de inicio.',
        ];
    }
}