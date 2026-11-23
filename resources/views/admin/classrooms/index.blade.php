<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grupos</h2>
            <a href="{{ route('admin.classrooms.create') }}"
               class="btn btn-sm">
                + Nuevo grupo
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <p class="mb-4 rounded-md bg-green-50 p-3 text-green-800" role="status">
                    {{ session('status') }}
                </p>
            @endif

            @forelse ($classrooms as $classroom)
                <div class="mb-3 rounded-lg bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between sm:gap-4">
                    <div>
                        <p class="font-medium">{{ $classroom->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $classroom->age_range ?? 'Sin edades' }} · {{ $classroom->children_count }} niños
                            · Director: {{ $classroom->director?->name ?? 'sin asignar' }}
                        </p>
                    </div>

                    <div x-data="{ confirming: false }" class="mt-3 sm:mt-0">
                        <div x-show="! confirming" class="flex gap-2">
                            <x-icon-button icon="edit" label="Editar" :context="$classroom->name" :href="route('admin.classrooms.edit', $classroom)" />
                            <x-icon-button icon="list" :label="$classroom->level?->isPreschool() ? 'Dimensiones' : 'Materias'" :context="$classroom->name" :href="route('admin.classrooms.subjects', $classroom)" />
                            <x-icon-button icon="trash" variant="danger" label="Quitar" :context="$classroom->name" x-on:click="confirming = true" />
                        </div>
                        {{-- Confirmación en la misma fila (reemplaza el confirm() del navegador). --}}
                        <form x-show="confirming" x-cloak method="POST" action="{{ route('admin.classrooms.destroy', $classroom) }}"
                              class="flex flex-wrap items-center gap-2" role="alertdialog" aria-label="Confirmar que quitas {{ $classroom->name }}">
                            @csrf
                            @method('DELETE')
                            <p class="w-full text-sm font-bold text-bad sm:w-auto">¿Quitar {{ $classroom->name }}? Sus niños quedarán sin grupo.</p>
                            <button type="submit" class="min-h-[44px] rounded-xl border-2 border-bad bg-bad px-4 text-sm font-bold text-white">Sí, quitar</button>
                            <button type="button" x-on:click="confirming = false" class="min-h-[44px] rounded-xl border-2 border-navy px-4 text-sm font-bold text-navy">Cancelar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Aún no hay grupos. Crea el primero.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>