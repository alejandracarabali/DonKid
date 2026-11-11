<div>
    <x-input-label for="name" value="Nombre del grupo" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
        :value="old('name', $classroom->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="age_range" value="Edades (opcional)" />
    <x-text-input id="age_range" name="age_range" type="text" class="block mt-1 w-full"
        placeholder="Ej: 1 a 2 años"
        :value="old('age_range', $classroom->age_range ?? '')" />
    <x-input-error :messages="$errors->get('age_range')" class="mt-2" />
</div>