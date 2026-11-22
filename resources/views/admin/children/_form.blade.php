<div>
    <x-input-label for="first_name" value="Nombre" />
    <x-text-input id="first_name" name="first_name" type="text" class="block mt-1 w-full"
        :value="old('first_name', $child->first_name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="last_name" value="Apellido" />
    <x-text-input id="last_name" name="last_name" type="text" class="block mt-1 w-full"
        :value="old('last_name', $child->last_name ?? '')" required />
    <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="birth_date" value="Fecha de nacimiento" />
    <x-text-input id="birth_date" name="birth_date" type="date" class="block mt-1 w-full"
        :value="old('birth_date', isset($child) ? $child->birth_date->format('Y-m-d') : '')" required />
    <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="classroom_id" value="Grupo" />
    <select id="classroom_id" name="classroom_id"
        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        <option value="">Sin grupo</option>
        @foreach ($classrooms as $classroom)
            <option value="{{ $classroom->id }}"
                @selected(old('classroom_id', $child->classroom_id ?? '') == $classroom->id)>
                {{ $classroom->name }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="allergies" value="Alergias (opcional)" />
    <textarea id="allergies" name="allergies" rows="3"
        class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
        placeholder="Ej: maní, lactosa">{{ old('allergies', $child->allergies ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('allergies')" class="mt-2" />
</div>

{{-- RF-38: ficha de salud. Solo la ven coordinación y las docentes del grupo. --}}
@php($health = isset($child) ? $child->healthRecord : null)
<fieldset class="mt-6 rounded-xl border-2 border-line p-4">
    <legend class="px-1 font-semibold">Ficha de salud</legend>
    <p class="text-sm text-soft">Datos sensibles: los ven coordinación y las docentes del grupo.</p>

    <div class="mt-3">
        <x-input-label for="health_eps" value="EPS" />
        <x-text-input id="health_eps" name="health[eps]" type="text" class="block mt-1 w-full" maxlength="80"
            :value="old('health.eps', $health?->eps)" />
        <x-input-error :messages="$errors->get('health.eps')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="health_conditions" value="Condiciones especiales (opcional)" />
        <textarea id="health_conditions" name="health[conditions]" rows="2" maxlength="500"
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Ej: asma leve">{{ old('health.conditions', $health?->conditions) }}</textarea>
        <x-input-error :messages="$errors->get('health.conditions')" class="mt-2" />
    </div>

    <div class="mt-4">
        <x-input-label for="health_medications" value="Medicamentos (opcional)" />
        <textarea id="health_medications" name="health[medications]" rows="2" maxlength="500"
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            placeholder="Ej: salbutamol, 2 inhalaciones si hay tos">{{ old('health.medications', $health?->medications) }}</textarea>
        <x-input-error :messages="$errors->get('health.medications')" class="mt-2" />
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2">
        <div>
            <x-input-label for="health_ec_name" value="Contacto de emergencia" />
            <x-text-input id="health_ec_name" name="health[emergency_contact_name]" type="text" class="block mt-1 w-full" maxlength="80"
                :value="old('health.emergency_contact_name', $health?->emergency_contact_name)" />
            <x-input-error :messages="$errors->get('health.emergency_contact_name')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="health_ec_phone" value="Teléfono de emergencia" />
            <x-text-input id="health_ec_phone" name="health[emergency_contact_phone]" type="tel" inputmode="tel" class="block mt-1 w-full" maxlength="20"
                :value="old('health.emergency_contact_phone', $health?->emergency_contact_phone)" />
            <x-input-error :messages="$errors->get('health.emergency_contact_phone')" class="mt-2" />
        </div>
    </div>
</fieldset>
