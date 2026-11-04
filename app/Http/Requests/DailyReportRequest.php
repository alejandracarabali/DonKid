<?php

namespace App\Http\Requests;

use App\Enums\MealStatus;
use App\Enums\Mood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyReportRequest extends FormRequest
{
    // La autorización real la hace la política en el controlador.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mood' => ['nullable', Rule::enum(Mood::class)],
            'meal_status' => ['nullable', Rule::enum(MealStatus::class)],
            'nap_minutes' => ['nullable', 'integer', 'min:0', 'max:480'],
            'note' => ['nullable', 'string', 'max:280'],
        ];
    }

    public function messages(): array
    {
        return [
            'mood.enum' => 'Elige uno de los estados de ánimo.',
            'meal_status.enum' => 'Elige una de las opciones de comida.',
            'nap_minutes.integer' => 'Escribe los minutos con números.',
            'nap_minutes.max' => 'La siesta no puede superar 8 horas.',
            'note.max' => 'La nota admite hasta 280 caracteres.',
        ];
    }
}