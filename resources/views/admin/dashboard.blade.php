<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel de coordinación
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <a href="{{ route('admin.classrooms.index') }}"
               class="block rounded-lg bg-white p-6 shadow-sm hover:bg-gray-50">
                <p class="font-medium">Grupos</p>
                <p class="text-sm text-gray-500">Crea y organiza los grupos del jardín.</p>
            </a>
            <a href="{{ route('admin.children.index') }}"
               class="mt-4 block rounded-lg bg-white p-6 shadow-sm hover:bg-gray-50">
                <p class="font-medium">Niños</p>
                <p class="text-sm text-gray-500">Registra a los niños y asígnalos a un grupo.</p>
            </a>
            <a href="{{ route('admin.guardians.index') }}"
               class="mt-4 block rounded-lg bg-white p-6 shadow-sm hover:bg-gray-50">
                <p class="font-medium">Familias</p>
                <p class="text-sm text-gray-500">Crea cuentas y vincúlalas a sus hijos.</p>
            </a>
            <a href="{{ route('admin.educators.index') }}"
               class="mt-4 block rounded-lg bg-white p-6 shadow-sm hover:bg-gray-50">
                <p class="font-medium">Educadoras</p>
                <p class="text-sm text-gray-500">Crea cuentas y asígnalas a sus grupos.</p>
            </a>
            <a href="{{ route('educator.reports.index') }}"
                class="inline-block bg-indigo-600 text-white px-4 py-3 rounded">
                Bitácora de hoy
            </a>
        </div>
    </div>
</x-app-layout>