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

{{-- RF-54: el nivel define, entre otras cosas, si el grupo lleva bitácora diaria. --}}
@php($levels = \App\Enums\GradeLevel::forSchool(auth()->user()->school?->type))
<div class="mt-4">
    <x-input-label for="level" value="Nivel" />
    <select id="level" name="level" required
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        <option value="">Elige el nivel</option>
        @foreach ($levels as $level)
            <option value="{{ $level->value }}" @selected(old('level', isset($classroom) ? $classroom->level?->value : null) === $level->value)>
                {{ $level->label() }}
            </option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('level')" class="mt-2" />
</div>
