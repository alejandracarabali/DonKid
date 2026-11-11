<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Ponle un título corto al aviso.',
            'title.max' => 'El título admite hasta 120 caracteres.',
            'body.required' => 'Escribe el mensaje para las familias.',
            'body.max' => 'El mensaje admite hasta 2000 caracteres.',
            'classroom_id.exists' => 'Ese grupo no existe.',
        ];
    }
}