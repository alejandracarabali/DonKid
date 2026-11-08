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

            <p class="text-gray-600">Aún no hay novedades. Te avisamos cuando haya.</p>

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