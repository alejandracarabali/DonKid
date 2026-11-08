<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Editar actividad</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form method="POST" action="{{ route('educator.activities.update', $activity) }}"
                  class="bg-white p-6 rounded shadow space-y-4">
                @method('PUT')
                @include('educator.activities._form')
            </form>
        </div>
    </div>
</x-app-layout>