@csrf

<div>
    <x-input-label for="classroom_id" value="Grupo" />
    @isset($activity)
        <p class="mt-1 text-gray-700">{{ $activity->classroom->name }}</p>
        <input type="hidden" name="classroom_id" value="{{ $activity->classroom_id }}">
    @else
        <select id="classroom_id" name="classroom_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            @foreach ($classrooms as $classroom)
                <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>
                    {{ $classroom->name }}
                </option>
            @endforeach
        </select>
    @endisset
    <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
</div>

<div>
    <x-input-label for="title" value="Título" />
    <x-text-input id="title" name="title" class="mt-1 block w-full" maxlength="120"
                  :value="old('title', $activity->title ?? '')" />
    <x-input-error :messages="$errors->get('title')" class="mt-2" />
</div>

<div>
    <x-input-label for="description" value="Descripción (opcional)" />
    <textarea id="description" name="description" rows="3" maxlength="500"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('description', $activity->description ?? '') }}</textarea>
    <x-input-error :messages="$errors->get('description')" class="mt-2" />
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="starts_at" value="Empieza" />
        <input id="starts_at" name="starts_at" type="datetime-local"
               value="{{ old('starts_at', isset($activity) ? $activity->starts_at->format('Y-m-d\TH:i') : '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        <x-input-error :messages="$errors->get('starts_at')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Termina" />
        <input id="ends_at" name="ends_at" type="datetime-local"
               value="{{ old('ends_at', isset($activity) ? $activity->ends_at->format('Y-m-d\TH:i') : '') }}"
               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
        <x-input-error :messages="$errors->get('ends_at')" class="mt-2" />
    </div>
</div>

<x-primary-button>Guardar actividad</x-primary-button>