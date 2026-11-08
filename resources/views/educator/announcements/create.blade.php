<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nuevo aviso</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($classrooms->isEmpty() && auth()->user()->role->value !== 'admin')
                <div class="bg-white p-6 rounded shadow text-gray-700">
                    Aún no tienes un grupo asignado. Pídele a coordinación que te asigne uno.
                </div>
            @else
                <form method="POST" action="{{ route('educator.announcements.store') }}"
                      class="bg-white p-6 rounded shadow space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="classroom_id" value="¿A quién va?" />
                        <select id="classroom_id" name="classroom_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            @if (auth()->user()->role->value === 'admin')
                                <option value="">Todas las familias</option>
                            @endif
                            @foreach ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('classroom_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="title" value="Título" />
                        <x-text-input id="title" name="title" class="mt-1 block w-full"
                                      :value="old('title')" maxlength="120" />
                        <x-input-error :messages="$errors->get('title')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="body" value="Mensaje" />
                        <textarea id="body" name="body" rows="5" maxlength="2000"
                                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>

                    <x-primary-button>Enviar aviso</x-primary-button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>