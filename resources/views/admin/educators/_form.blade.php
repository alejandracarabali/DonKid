@php
    $selected = old('classrooms', isset($educator) ? $educator->classrooms->pluck('id')->all() : []);
@endphp

<div>
    <x-input-label for="name" value="Nombre" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
        :value="old('name', $educator->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" value="Correo" />
    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full"
        :value="old('email', $educator->email ?? '')" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<fieldset class="mt-6">
    <legend class="text-sm font-medium text-gray-700">Grupos que atiende</legend>
    <x-input-error :messages="$errors->get('classrooms.*')" class="mt-2" />

    @forelse ($classrooms as $classroom)
        <div class="mt-2 flex items-center gap-3">
            <input type="checkbox" id="classroom-{{ $classroom->id }}" name="classrooms[]"
                value="{{ $classroom->id }}" @checked(in_array($classroom->id, $selected))
                class="rounded border-gray-300">
            <label for="classroom-{{ $classroom->id }}">{{ $classroom->name }}</label>
        </div>
    @empty
        <p class="mt-2 text-sm text-gray-500">Aún no hay grupos. Crea uno primero.</p>
    @endforelse
</fieldset>