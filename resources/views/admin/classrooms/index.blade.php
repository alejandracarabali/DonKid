<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Grupos</h2>
            <a href="{{ route('admin.classrooms.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-md text-sm">
                Nuevo grupo
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
                <div class="mb-3 flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-medium">{{ $classroom->name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $classroom->age_range ?? 'Sin edades' }} · {{ $classroom->children_count }} niños
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="text-sm underline">Editar</a>

                        <form method="POST" action="{{ route('admin.classrooms.destroy', $classroom) }}"
                              onsubmit="return confirm('¿Quitar este grupo? Sus niños quedarán sin grupo.')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-600 underline">Quitar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Aún no hay grupos. Crea el primero.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>