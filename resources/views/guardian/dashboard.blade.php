<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Hola, {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 text-green-800 p-3 rounded">{{ session('status') }}</div>
            @endif

            <section class="space-y-3">
                <h3 class="font-semibold text-gray-800">Mis hijos</h3>

                @forelse ($children as $child)
                    <div class="bg-white p-4 rounded shadow flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-medium">{{ $child->first_name }} {{ $child->last_name }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $child->classroom?->name ?? 'Sin grupo asignado' }}
                            </p>
                        </div>
                        <a href="{{ route('guardian.reports.index', $child) }}"
                           class="inline-block bg-indigo-600 text-white px-4 py-2 rounded">
                            Ver la bitácora de {{ $child->first_name }}
                        </a>
                    </div>
                @empty
                    <div class="bg-white p-4 rounded shadow text-gray-700">
                        Aún no tienes hijos vinculados. Pídele a coordinación que los asocie a tu cuenta.
                    </div>
                @endforelse
            </section>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('guardian.announcements.index') }}"
                   class="inline-block bg-indigo-600 text-white px-4 py-3 rounded">
                    Avisos
                </a>
                <a href="{{ route('guardian.activities.index') }}"
                   class="inline-block bg-indigo-600 text-white px-4 py-3 rounded">
                    Actividades
                </a>
            </div>
        </div>
    </div>
</x-app-layout>