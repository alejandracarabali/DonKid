<?php

namespace App\Http\Requests;

use App\Enums\SchoolType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PlatformSchoolRequest extends FormRequest
{
    // El rol ya lo revisa la ruta (role:superadmin).
    public function authorize(): bool
    {
        return true;
    }

    // Si no escriben el slug, se arma con el nombre: "Jardín Los Pinos" → "jardin-los-pinos".
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('name', ''))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'unique:schools,slug'],
            'type' => ['required', Rule::enum(SchoolType::class)],
            'plan' => ['nullable', 'string', 'max:30'],
            'admin_name' => ['required', 'string', 'max:80'],
            'admin_email' => ['required', 'email', 'max:120', 'unique:users,email'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Escribe el nombre del colegio.',
            'slug.unique' => 'Ya hay un colegio con esa dirección. Cambia el slug.',
            'slug.regex' => 'El slug solo puede tener minúsculas, números y guiones.',
            'type.required' => 'Elige el tipo de institución.',
            'admin_name.required' => 'Escribe el nombre de quien coordina.',
            'admin_email.required' => 'Escribe el correo de quien coordina.',
            'admin_email.unique' => 'Ese correo ya tiene una cuenta en DonKid.',
        ];
    }
}
