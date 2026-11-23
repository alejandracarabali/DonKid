<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Niños</h2>
            <div class="flex items-center gap-4">
                {{-- Acción de página: ícono + texto (no solo ícono). --}}
                <a href="{{ route('admin.children.withdrawn') }}"
                   class="inline-flex min-h-[44px] items-center gap-2 rounded-xl border-2 border-line bg-canvas px-4 text-sm font-bold text-navy hover:border-navy focus:outline-none focus-visible:ring-2 focus-visible:ring-action">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 5h18v4H3z"/><path d="M5 9v10h14V9"/><path d="M10 13h4"/></svg>
                    Ver retirados
                </a>
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

                    <div class="flex gap-2">
                        <x-icon-button icon="edit" label="Editar" :context="$child->first_name.' '.$child->last_name" :href="route('admin.children.edit', $child)" />
                        {{-- Retirar pide fecha y motivo (RF-53): lleva a esa sección de Editar, no retira de una vez. --}}
                        <x-icon-button icon="user-minus" variant="danger" label="Retirar" :context="$child->first_name.' '.$child->last_name"
                                       :href="route('admin.children.edit', $child).'#retirar'" />
                    </div>
                </div>
            @empty
                <p class="text-gray-600">Aún no hay niños. Agrega el primero.</p>
            @endforelse

            {{ $children->links() }}
        </div>
    </div>
</x-app-layout>