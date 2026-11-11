<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Nueva actividad</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($classrooms->isEmpty())
                <div class="bg-white p-6 rounded shadow text-gray-700">
                    Aún no tienes un grupo asignado. Pídele a coordinación que te asigne uno.
                </div>
            @else
                <form method="POST" action="{{ route('educator.activities.store') }}"
                      class="bg-white p-6 rounded shadow space-y-4">
                    @include('educator.activities._form')
                </form>
            @endif
        </div>
    </div>
</x-app-layout>