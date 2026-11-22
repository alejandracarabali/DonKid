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

{{-- RF-61: director de grupo. Lleva la bitácora y es la cara del grupo ante las familias. --}}
<div class="mt-4">
    <x-input-label for="director_id" value="Director de grupo" />
    <select id="director_id" name="director_id"
            class="block mt-1 w-full border-gray-300 rounded-md shadow-sm">
        <option value="">Sin asignar</option>
        @foreach ($educators as $educator)
            <option value="{{ $educator->id }}" @selected((string) old('director_id', isset($classroom) ? $classroom->director_id : '') === (string) $educator->id)>
                {{ $educator->name }}
            </option>
        @endforeach
    </select>
    <p class="mt-1 text-sm text-gray-500">Solo el director registra la bitácora del grupo y recibe los mensajes de las familias.</p>
    <x-input-error :messages="$errors->get('director_id')" class="mt-2" />
</div>
