@php
    // Niños ya vinculados (al editar): [id => parentesco]
    $linked = isset($guardian)
        ? $guardian->children->pluck('pivot.relationship', 'id')->all()
        : [];
    $selected = old('children', array_keys($linked));
@endphp

<div>
    <x-input-label for="name" value="Nombre" />
    <x-text-input id="name" name="name" type="text" class="block mt-1 w-full"
        :value="old('name', $guardian->name ?? '')" required autofocus />
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="email" value="Correo" />
    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full"
        :value="old('email', $guardian->email ?? '')" required />
    <x-input-error :messages="$errors->get('email')" class="mt-2" />
</div>

<fieldset class="mt-6">
    <legend class="text-sm font-medium text-gray-700">Niños de esta familia</legend>
    <x-input-error :messages="$errors->get('children.*')" class="mt-2" />

    @forelse ($children as $child)
        <div class="mt-2 flex items-center gap-3">
            <input type="checkbox" id="child-{{ $child->id }}" name="children[]"
                value="{{ $child->id }}" @checked(in_array($child->id, $selected))
                class="rounded border-gray-300">
            <label for="child-{{ $child->id }}" class="w-48">
                {{ $child->first_name }} {{ $child->last_name }}
            </label>
            <input type="text" name="relationships[{{ $child->id }}]"
                value="{{ old('relationships.'.$child->id, $linked[$child->id] ?? '') }}"
                placeholder="Parentesco (Mamá, Abuela…)"
                aria-label="Parentesco con {{ $child->first_name }}"
                class="block w-full border-gray-300 rounded-md shadow-sm text-sm">
        </div>
    @empty
        <p class="mt-2 text-sm text-gray-500">Aún no hay niños. Registra uno primero.</p>
    @endforelse
</fieldset>