<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Niños</h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.children.withdrawn') }}" class="text-sm underline">Ver retirados</a>
                <a href="{{ route('admin.children.create') }}"
                   class="btn btn-sm">
                    + Nuevo niño
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

            @if (session('status'))
                <p class="mb-4 rounded-md bg-green-50 p-3 text-green-800" role="status">
                    {{ session('status') }}
                </p>
            @endif

            @forelse ($children as $child)
                <div class="mb-3 flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-medium">{{ $child->first_name }} {{ $child->last_name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $child->classroom?->name ?? 'Sin grupo' }}
                            · {{ $child->birth_date->age }} años
                        </p>
                    </div>

                    <div class="flex items-center gap-4">
                        {{-- Retirar está dentro de Editar: pide fecha y motivo. --}}
                        <a href="{{ route('admin.children.edit', $child) }}" class="text-sm underline">Editar o retirar</a>
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Aún no hay niños. Agrega el primero.</p>
            @endforelse

            {{ $children->links() }}
        </div>
    </div>
</x-app-layout>