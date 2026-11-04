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