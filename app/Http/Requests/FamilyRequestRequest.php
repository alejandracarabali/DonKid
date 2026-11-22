<?php

namespace App\Http\Requests;

use App\Enums\FamilyRequestType;
use App\Models\FamilyRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FamilyRequestRequest extends FormRequest
{
    // El permiso (que sea su hijo) lo revisa el controlador.
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $type = FamilyRequestType::tryFrom($this->input('type'));
        $pickup = $type === FamilyRequestType::Permission && $this->input('reason') === 'other_person';

        return [
            'type' => ['required', Rule::enum(FamilyRequestType::class)],

            'date' => $type === FamilyRequestType::Recommendation
                ? ['prohibited']
                // Una excusa puede ser por días pasados (hasta 30); un permiso, solo hoy o después.
                : ['required', 'date', $type === FamilyRequestType::Permission ? 'after_or_equal:today' : 'after_or_equal:'.now()->subDays(30)->toDateString()],
            'time' => ['nullable', 'date_format:H:i'],

            'reason' => match ($type) {
                FamilyRequestType::Excuse => ['required', Rule::in(FamilyRequest::EXCUSE_REASONS)],
                FamilyRequestType::Permission => ['required', Rule::in(array_keys(FamilyRequest::PERMISSION_KINDS))],
                default => ['prohibited'],
            },
            'note' => [$type === FamilyRequestType::Recommendation ? 'required' : 'nullable', 'string', 'max:280'],

            'pickup_name' => $pickup ? ['required', 'string', 'max:80'] : ['prohibited'],
            'pickup_relationship' => $pickup ? ['required', Rule::in(FamilyRequest::RELATIONSHIPS)] : ['prohibited'],
            'pickup_phone' => $pickup ? ['required', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/'] : ['prohibited'],
        ];
    }

    // Campos que no aplican al tipo elegido: el navegador los manda vacíos, se descartan.
    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->only(['date', 'time', 'reason', 'note', 'pickup_name', 'pickup_relationship', 'pickup_phone']))
            ->map(fn ($v) => $v === '' ? null : $v)->all());

        $clean = array_filter($this->all(), fn ($v) => $v !== null);
        $this->replace($clean);
    }

    public function messages(): array
    {
        return [
            'date.required' => 'Elige la fecha.',
            'date.after_or_equal' => 'Revisa la fecha.',
            'time.date_format' => 'Revisa la hora.',
            'reason.required' => 'Elige una opción.',
            'note.required' => 'Escribe la recomendación.',
            'note.max' => 'Es muy largo. Máximo 280 caracteres.',
            'pickup_name.required' => 'Escribe el nombre de quien recoge.',
            'pickup_relationship.required' => 'Elige el parentesco.',
            'pickup_phone.required' => 'Escribe un teléfono.',
            'pickup_phone.regex' => 'Revisa el teléfono: solo números, mínimo 7.',
        ];
    }
}
