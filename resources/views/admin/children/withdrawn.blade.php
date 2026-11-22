<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Estudiantes retirados</h2>
            <a href="{{ route('admin.children.index') }}" class="text-sm underline">Volver a activos</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @forelse ($children as $child)
                <div class="mb-3 flex items-center justify-between rounded-lg bg-white p-4 shadow-sm">
                    <div>
                        <p class="font-medium">{{ $child->first_name }} {{ $child->last_name }}</p>
                        <p class="text-sm text-gray-500">
                            {{ $child->classroom?->name ?? 'Sin grupo' }}
                            · Retirado el {{ $child->withdrawn_at->format('d/m/Y') }}
                            @if ($child->withdrawal_reason)
                                · {{ $child->withdrawal_reason }}
                            @endif
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.children.reactivate', $child->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="text-sm underline">Reactivar</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-600">No hay estudiantes retirados.</p>
            @endforelse

            {{ $children->links() }}
        </div>
    </div>
</x-app-layout>
